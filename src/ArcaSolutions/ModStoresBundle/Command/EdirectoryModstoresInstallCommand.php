<?php

namespace ArcaSolutions\ModStoresBundle\Command;

use AppKernel;
use ArcaSolutions\CoreBundle\Entity\Domain;
use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\CoreBundle\Repository\DomainRepository;
use ArcaSolutions\ModStoresBundle\Kernel\Kernel as PluginKernel;
use ArcaSolutions\ModStoresBundle\ModStoresBundle;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle;
use ArcaSolutions\ModStoresBundle\Services\AutoloaderCache;
use ArcaSolutions\ModStoresBundle\Services\VersionControl;
use ArcaSolutions\MultiDomainBundle\Command\AbstractMultiDomainCommand;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\MultiDomainBundle\Services\Settings;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Exception;
use InvalidArgumentException;
use LogicException;
use ReflectionException;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\ConsoleEvents;


class SpecificDomainCliAppKernel extends AppKernel{
    protected $_cacheDir;
    public function __construct($environment, $debug, $domain, $appRootDir, $cacheDir)
    {
        parent::__construct($environment, $debug);
        $this->domain = $domain;
        $this->rootDir = $appRootDir;
        $this->_cacheDir = $cacheDir;
    }

    public function getCacheDir()
    {
        return $this->_cacheDir;
    }

    protected function initializeContainer()
    {
        $domain = str_replace(array('www.', '.', '-'), array('', '_', '_'), $this->domain);
        $class = $this->getContainerClass() . '_' . $domain . '_cli';
        $cache = new ConfigCache($this->getCacheDir().'/'.$class.'.php', $this->debug);

        $fresh = true;
        if (!$cache->isFresh()) {
            $container = $this->buildContainer();
            $container->compile();
            $this->dumpContainer($cache, $container, $class, $this->getContainerBaseClass());
            $fresh = false;
        }

        require_once $cache->getPath();

        $this->container = new $class();
        $this->container->set('kernel', $this);
        /** @var Settings $multiDomainSettingsService */
        $multiDomainSettingsService = $this->container->get('multi_domain.information');
        $multiDomainSettingsService->setActiveHost($this->domain);
        $multiDomainSettingsService->setActiveHostById($multiDomainSettingsService->getId() , true);

        if (!$fresh && $this->container->has('cache_warmer')) {
            $this->container->get('cache_warmer')->warmUp($this->container->getParameter('kernel.cache_dir'));
        }
    }
}

class WriteCallbackBufferedOutput extends BufferedOutput{
    /**
     * @var callable|null $_writeCallback
     */
    private $writeCallback = null;

    /**
     * Constructor.
     *
     * @param callable $writeCallback
     * @param int $verbosity The verbosity level (one of the VERBOSITY constants in OutputInterface)
     * @param bool $decorated Whether to decorate messages
     * @param OutputFormatterInterface|null $formatter Output formatter instance (null to use default OutputFormatter)
     */
    public function __construct($writeCallback, $verbosity = self::VERBOSITY_NORMAL, $decorated = false, OutputFormatterInterface $formatter = null)
    {
        $this->writeCallback = $writeCallback;
        parent::__construct($verbosity,$decorated,$formatter);
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline)
    {
        $messageAfterCall = $message;
        $newlineAfterCall = $newline;
        call_user_func($this->writeCallback, $messageAfterCall, $newlineAfterCall);
        parent::doWrite($messageAfterCall, $newlineAfterCall);
    }
}

/**
 * Class EdirectoryModstoresInstallCommand
 *
 * @package ArcaSolutions\ModStoresBundle\Kernel
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 * @author José Lourenção <jose.lourencao@arcasolutions.com>
 * @author Fábio Mastelari <fabio.leite@arcasolutions.com>
 *
 * Errors Debug Troubleshot
 *
 * [1001] - Edirectory version does not match with Kernel requirements
 * [1002] - Kernel version does not match with plugin requirements
 */
class EdirectoryModstoresInstallCommand extends AbstractMultiDomainCommand
{
    /**
     * @var int
     */
    private $countInstalled = 0;

    private $allDomains = false;

    private $targetDomain = null;

    private $targetEnv = null;

    private $targetDebug = false;

    private $ignoreElasticSync = false;

    private $ignoreGulp = false;

    private $justGulp = false;

    private $bufferedOutput = null;

    private $skippedDomains = [];

    /**
     * @var SymfonyStyle $io
     */
    private $io = null;

    /**
     * @var array
     */
    private $requiredList = [
        'beforeCommand' => [],
        'afterCommand'  => [],
    ];

    /**
     * @var array
     */
    private $domainDatabases = null;

    /**
     * Edirectory ModStore Core Installation Command base configuration
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('edirectory:plugin:install');
        $this->setDescription('Install eDirectory Plugin');
        $this->addOption(
            'execute-required',
            'r',
            InputOption::VALUE_NONE,
            'Execute all required commands after installation.'
        );
        $this->addOption(
            'upgrade',
            'u',
            InputOption::VALUE_NONE,
            'For ModStore upgrade if available on bundle.'
        );
        $this->addOption(
            'force-install',
            'f',
            InputOption::VALUE_NONE,
            'For ModStore to force re-install on bundle if at least same version.'
        );
        $this->addOption(
            'lang',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Set up project language.'
        );
        $this->addOption(
            'no-elastic-sync',
            'nes',
            InputOption::VALUE_NONE,
            'Do not execute elastic sync commands.'
        );
        $this->addOption(
            'no-gulp',
            'ng',
            InputOption::VALUE_NONE,
            'Do not execute gulp commands.'
        );

        $this->addOption(
            'gulp-only',
            'go',
            InputOption::VALUE_NONE,
            'Just execute gulp commands.'
        );

        $this->setAliases([
            'edirectory:modstores:install',
        ]);
    }

    /**
     * Edirectory ModStore Core Installation Command base execute method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws ReflectionException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->skippedDomains = [];

        if ($input->hasOption('no-gulp')&&$input->hasOption('gulp-only')) {
            if ($input->getOption('no-gulp') && $input->getOption('gulp-only')) {
                throw new InvalidArgumentException('Cannot set both "no-gulp" and "gulp-only" for command execution.');
            }
        }
        if ($input->hasOption('all-domains')&&$input->hasOption('domain')) {
            if ($input->getOption('all-domains') && $input->getOption('domain')) {
                throw new InvalidArgumentException('Cannot set both "all-domains" and "domain" for command execution.');
            }
        }

        if ($input->hasOption('no-gulp')){
            $this->ignoreGulp = $input->getOption('no-gulp');
        }

        if ($input->hasOption('gulp-only')){
            $this->justGulp = $input->getOption('gulp-only');
        }

        if ($input->hasOption('no-elastic-sync')){
            $this->ignoreElasticSync = $input->getOption('no-elastic-sync');
        }

        if ($input->hasOption('all-domains')) {
            $this->allDomains = $input->getOption('all-domains');
        }
        if ($input->hasOption('domain')) {
            $this->targetDomain = $input->getOption('domain');
        }

        if(empty($this->targetDomain) && !$this->allDomains){
            $this->allDomains = true;
        }

        if ($input->hasOption('env')) {
            $this->targetEnv = $input->getOption('env');
        }

        $this->targetDebug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(array('--no-debug', '')) && $this->targetEnv !== 'prod';

        $application = $this->getApplication();
        if(!empty($application)) {
            /**
             * @var Kernel $kernel
             */
            $kernel = $application->getKernel();
            if (!empty($kernel) && $kernel instanceof Kernel) {
                if ($this->targetEnv === null || ($this->targetEnv !== 'dev' && $this->targetEnv !== 'test' && $this->targetEnv !== 'prod')) {
                    $this->targetEnv = $kernel->getEnvironment();
                }
            }
            unset($kernel);
        }
        unset($application);

        $this->io = new SymfonyStyle($input, $output);

        // writes nice command header
        $this->io->block('eDirectory Plugins Installation', null, 'fg=black;bg=green', ' ', true);

        /**
         * @var ContainerInterface $container
         */
        $container = $this->getContainer();
        if(!empty($container)) {
            /**
             * @var VersionControl $pluginVersionControlService
             */
            $pluginVersionControlService = $container->get('modstore.versioncontrol.service');
            /**
             * @var ModStoresBundle $pluginBundle
             */
            $pluginBundle = $container->get('kernel.modstore');
            if(!empty($pluginVersionControlService)) {
                // validates if kernel matches with edirectory version
                if (!$pluginVersionControlService->isValidModStoreVersion()) {
                    if (!empty($pluginBundle)) {
                        $this->io->writeln(sprintf('<error>[1001] Invalid %s version</error>', $pluginBundle->getName()));
                    } else {
                        $this->io->writeln(sprintf('<error>[1002] Invalid plugin version and system was unable to determine plugin name.</error>'));
                    }
                    $this->io->newLine();
                    unset($container, $pluginVersionControlService, $pluginBundle);
                    exit;
                }

                if (!empty($pluginBundle)) {
                    /**
                     * @var PluginKernel $pluginKernel
                     */
                    $pluginKernel = $pluginBundle->getKernel();
                    if (!empty($pluginKernel)) {
                        // continue with activated plugins
                        $activated = $pluginKernel->getActivated();
                        if (!empty($activated) && is_array($activated)) {
                            // gets current installed ModStores
                            $installed = [];
                            // run all base commands for all plugins
                            /**
                             * @var Bundle $plugin
                             */
                            foreach ($activated as $plugin) {
                                if ($pluginVersionControlService->isValidPluginVersion($plugin)) {
                                    try {
                                        if (!(in_array($plugin->getQualifiedNamespace(), $pluginKernel->getInstalled(true))) || $this->hasUpgrade($pluginVersionControlService, $pluginKernel, $input, $plugin)) {
                                            // add ModStore as installed and increments counter
                                            $installed[] = $plugin;
                                            $this->countInstalled++;
                                            /**
                                             * @var AbstractEdirectoryModstoresInstallCommand $command
                                             */
                                            $command = $plugin->getInstallCommand($input, $output);
                                            // execute needed method from command and get before command instructions
                                            $command->executeBeforeCommand();
                                            $this->addRequired('beforeCommand', $plugin->getName(), $command->getData('beforeCommand'));
                                            // execute needed method from command
                                            $command->executeCommand();
                                            // execute needed method from command and get after command instructions
                                            $command->executeAfterCommand();
                                            $this->addRequired('afterCommand', $plugin->getName(), $command->getData('afterCommand'));
                                            unset($command);
                                        }
                                    } catch (Exception $e){
                                        $this->io->writeln(sprintf('<error>[1003] Unexpected exception: %s</error>', $e->getMessage()));
                                        $this->io->writeln(sprintf('<error>       Exception trace: %s</error>', $e->getTraceAsString()));
                                        $this->io->newLine();
                                        continue;
                                    }
                                } else {
                                    $this->io->writeln(sprintf('<error>[1004] Invalid %s require version</error>', $plugin->getComposerMetadata('description')));
                                    $this->io->newLine();
                                }
                            }

                            try {
                                // saves installed ModStores
                                $this->updateInstalled($container, $installed);
                            } catch (Exception $e){
                                $this->io->writeln(sprintf('<error>[1005] Can not update eDirectory Plugins installed file. Exception: %s</error>', $e->getMessage()));
                                $this->io->writeln(sprintf('<error>       Exception trace: %s</error>', $e->getTraceAsString()));
                                $this->io->newLine();
                                unset($container, $pluginVersionControlService, $pluginBundle, $pluginKernel, $installed);
                                exit;
                            }
                            // run after installation commands
                            $this->executeAfterRequired($input);
                            unset($installed);
                        }
                        unset($activated);
                    } else {
                        $this->io->writeln(sprintf('<error>[1006] Can not access plugin kernel.</error>'));
                        $this->io->newLine();
                        unset($container, $pluginVersionControlService, $pluginBundle, $pluginKernel);
                        exit;
                    }
                    unset($pluginKernel);
                } else {
                    $this->io->writeln(sprintf('<error>[1007] Can not access plugin bundle.</error>'));
                    $this->io->newLine();
                    unset($container, $pluginVersionControlService, $pluginBundle);
                    exit;
                }
            } else {
                $this->io->writeln(sprintf('<error>[1008] Can not access plugin version service.</error>'));
                $this->io->newLine();
                unset($container, $pluginVersionControlService, $pluginBundle);
                exit;
            }
        }

        unset($container);
        // alert if any ModStores were installed or not
        if (!empty($this->countInstalled)) {
            $this->io->writeln(sprintf('<comment>Installation finished</comment>'));
        } else {
            $this->io->writeln(sprintf('<comment>There is no Plugins to install</comment>'));
        }
        $this->io->newLine();
    }

    /**
     * Compare versions for upgrade need verification
     *
     * @param VersionControl $versionControlService
     * @param PluginKernel $pluginKernel
     * @param InputInterface $input
     * @param AbstractPluginBundle $plugin
     * @return bool|mixed
     * @throws ReflectionException
     * @throws Exception
     */
    private function hasUpgrade(VersionControl &$versionControlService, PluginKernel &$pluginKernel, InputInterface &$input, &$plugin)
    {
        $installed = $pluginKernel->getVersionLock();
        if (!array_key_exists($plugin->getName(), $installed)) {
            return false;
        }
        $installedVersion = null;

        $pluginName = $plugin->getName();
        if(!empty($pluginName) && array_key_exists($pluginName, $installed) && array_key_exists('version', $installed[$pluginName])) {
            $installedVersion = $installed[$pluginName]['version'];
        }

        if (!empty($installedVersion)) {
            $returnValue = false;
            if ($input->getOption('force-install')) {
                $returnValue = $versionControlService->compareAGreaterEqual($plugin->getComposerMetadata('version'), $installedVersion);
            } else if ($input->getOption('upgrade')) {
                $returnValue = $versionControlService->compareAGreater($plugin->getComposerMetadata('version'), $installedVersion);
            }
            unset($installed, $pluginName, $installedVersion);
            return $returnValue;
        } else {
            unset($installed, $pluginName, $installedVersion);
            throw new Exception("Cannot determine installed plugin name or version.");
        }
    }

    /**
     * Add command(s) to queue
     *
     * @param string $queueName
     * @param string $bundleName
     * @param $commands
     */
    private function addRequired($queueName, $bundleName, $commands)
    {
        if(!empty($commands)) {
            // wrap commands to an array if it is not a list of items
            !is_array($commands) and $commands = [$commands => null];

            $bundleItemQueue = &$this->requiredList[$queueName][$bundleName];
            !isset($bundleItemQueue) and $bundleItemQueue = null;
            if (empty($bundleItemQueue)) {
                $bundleItemQueue = [];
            }

            foreach ($commands as $command => $params) {
                if($this->ignoreGulp && strpos($command, 'gulp')!==false){
                    continue;
                }
                if($this->justGulp && strpos($command,'gulp')===false){
                    continue;
                }
                if($this->ignoreElasticSync && (strpos($command, 'edirectory:sync')!==false||strpos($command, 'elastic:recreate-index')!==false)){
                    continue;
                }
                // copy reference of determined queue command to a pointer
                $itemQueue = &$bundleItemQueue[$command];

                // set item is not set, initialize it as null
                !isset($itemQueue) and $itemQueue = null;
                if (!empty($params)) {
                    if (empty($itemQueue)) {
                        $itemQueue = [];
                    }
                    if($params!==null) {
                        // wrap params to an array if it is not a list of items
                        !is_array($params) and $params = [$params => null];
                        $itemQueue = array_merge_recursive($itemQueue, $params);
                    }
                }
            }
        }
    }

    /**
     * Update installed cache
     *
     * @param ContainerInterface $container
     * @param $installed
     * @throws Exception
     */
    private function updateInstalled(&$container, &$installed)
    {
        if (!empty($this->countInstalled)) {
            /**
             * @var AutoloaderCache $autoloaderCacheService
             */
            $autoloaderCacheService = $container->get('modstore.autoloader.cache.service');
            if(!empty($autoloaderCacheService)) {
                $autoloaderCacheService->saveInstalled($installed);
            } else {
                throw new Exception('Unable to access Plugins AutoLoaderCache service.');
            }
        }
    }

    /**
     * Execute the pre-built proccess in the ProcessBuilder instance
     * @author Fábio Mastelari <fabio.leite@arcasolutions.com>
     *
     * @param ProcessBuilder $processBuilder
     * Do not use it to run Symfony commands. This method creates a new php proccess and loose the context of the
     * current command execution (eg.: php options, etc). If the php server forbids the php 'exec' function, this
     * method will hang.
     */
    protected function runProcessBuilder(&$processBuilder){
        if (!empty($processBuilder)) {
            // run built command
            /**
             * @var Process $process
             */
            $process = $processBuilder->getProcess();
            $process->setTty(true);//This will output console directly
            $commandMessage = $process->getCommandLine();
            if(!empty($this->io)) {
                $this->io->writeln('Running command: ' . $commandMessage);
            } else{
                echo 'Running command: ' . $commandMessage;
            }
            $process->run();
            unset($process, $commandMessage);
        }
    }

    /**
     * Runs a command considering the context of the given Application, using the given arrayInputDict to determine the command and parameters
     * @param $arrayInputDict
     * @param Application $applicationDef
     * @param $domainUrl
     * @throws \Doctrine\DBAL\DBALException
     * @author Fábio Mastelari <fabio.leite@arcasolutions.com>
     * This approach (create a new kernel, inject the actual kernel connection settings, add an listener to console exceptions,
     * and create a new application that will run the command) is the only that could be ensure the correct execution of an inner command.
     * The other approaches like: use command instance itself or their services or call it directly by the current application hangs when
     * execute migrations and does not allow to skip a command and continue to the next one.
     */
    protected function runApplicationCommand($arrayInputDict, &$applicationDef, string $domainUrl = null)
    {
        try {
            if($domainUrl!==null){
                if (!array_key_exists('--process-isolation', $arrayInputDict) || !$arrayInputDict['--process-isolation']) {
                    $arrayInputDict['--process-isolation'] = true;
                }
                $originKernel = $applicationDef->getKernel();
                $kernel = new SpecificDomainCliAppKernel($this->targetEnv, $this->targetDebug, $domainUrl, $originKernel->getRootDir(), $originKernel->getCacheDir());
                $kernel->boot();
            } else {
                $originKernel = $applicationDef->getKernel();
                $originContainer = $originKernel->getContainer();
                $originalDomainConnection = $originContainer->get('doctrine.dbal.domain_connection');

                $kernel = new AppKernel($this->targetEnv, $this->targetDebug);
                $kernel->boot();
                $kernel->getContainer()->set('doctrine.dbal.domain_connection', $originalDomainConnection);

                $domainConnection = $kernel->getContainer()->get('doctrine.dbal.domain_connection');
                if (!empty($domainConnection)) {
                    $domainConnectionParams = $domainConnection->getParams();
                    if ($domainConnection->isConnected()) {
                        $domainConnection->close();
                    }
                    try {
                        $domainConnection->__construct($domainConnectionParams, $domainConnection->getDriver(), $domainConnection->getConfiguration(), $domainConnection->getEventManager());
                        $domainConnection->connect();
                    } catch (Exception $e) {
                        throw $e;
                    }
                } else {
                    unset($domainConnection);
                    throw new LogicException('The doctrine dbal domain connection entry cannot be retrieved from container.');
                }
            }
            $dispatcher = $kernel->getContainer()->get('event_dispatcher');
            $dispatcher->addListener(ConsoleEvents::EXCEPTION, function (ConsoleExceptionEvent $event) {
                $eventException = $event->getException();
                if ($eventException instanceof FatalThrowableError) {
                    $nonFatalException = new Exception($eventException->getMessage(), $eventException->getCode(), $eventException);
                    $event->setException($nonFatalException);//When set exception to a non Fatal one, the system does not hang up all console application
                    unset($nonFatalException);
                }
                unset($eventException);
            }, 999);// This listener is needed to avoid installation sub-commands to hang up the installation command itself

            $application = new Application($kernel);
            $application->setAutoExit(false);
            $application->setCatchExceptions(false);
            $application->all();//Ensure internal load of the command list

            if (!empty($arrayInputDict) && array_key_exists('command', $arrayInputDict) && !empty($arrayInputDict['command'])) {
                if (empty($this->bufferedOutput)) {
                    $writeCallback = function ($message, $newline) {
                        $this->outputMessage($message, $newline);
                    };
                    $this->bufferedOutput = new WriteCallbackBufferedOutput($writeCallback);
                    $this->bufferedOutput->setDecorated(true);
                }
                $commandName = $arrayInputDict['command'];

                $application->setDefaultCommand($commandName, true);

                $command = null;
                $commandNotFound = false;
                try {
                    $command = $application->get($commandName);

                    $input = null;
                    $commandInputDef = $command->getDefinition();
                    if (!empty($commandInputDef)) {
                        $argumentInputDict = [];
                        foreach ($arrayInputDict as $dictKey => $dictValue) {
                            if (strpos($dictKey, '--') === 0) {
                                $dictKeyWithoutDashes = substr($dictKey, 2);
                                if (empty($dictKeyWithoutDashes) || !$commandInputDef->hasOption($dictKeyWithoutDashes)) {
                                    unset($dictKeyWithoutDashes);
                                    continue;
                                }
                                unset($dictKeyWithoutDashes);
                            } elseif ('-' === $dictKey[0]) {
                                $dictKeyWithoutDash = substr($dictKey, 1);
                                if (empty($dictKeyWithoutDash) || !$commandInputDef->hasShortcut($dictKeyWithoutDash)) {
                                    unset($dictKeyWithoutDash);
                                    continue;
                                }
                                unset($dictKeyWithoutDash);
                            } else {
                                if ((empty($dictKey) && $dictKey !== 0) || !$commandInputDef->hasArgument($dictKey)) {
                                    continue;
                                }
                            }
                            $argumentInputDict[$dictKey] = $dictValue;
                        }

                        $input = new ArrayInput($argumentInputDict, $commandInputDef);
                        unset($argumentInputDict);
                        $input->setInteractive(false);//Avoid system to hang-up waiting for user input
                    }
                    unset($commandInputDef);
                } catch (CommandNotFoundException $e) {
                    $commandNotFound = true;
                }

                $commandMessage = $commandName;
                unset($commandName);
                foreach ($arrayInputDict as $cmdOption => $cmdOptionValue) {
                    if ($cmdOption === 'command') {
                        continue;
                    }
                    if ($cmdOptionValue !== true && !empty($cmdOptionValue)) {
                        if (is_array($cmdOptionValue)) {
                            foreach ($cmdOptionValue as $cmdOptionValueItem) {
                                if (!empty($cmdOptionValueItem)) {
                                    $commandMessage .= ' ' . $cmdOption . '=' . $cmdOptionValueItem;
                                }
                            }
                        } else {
                            $commandMessage .= ' ' . $cmdOption . '=' . $cmdOptionValue;
                        }
                    } else {
                        $commandMessage .= ' ' . $cmdOption;
                    }
                }
                if (!empty($command) && !empty($input)) {
                    $this->outputMessage('Running command: ' . $commandMessage);

                    sleep (0.25);
                    $returnCode = $application->run($input, $this->bufferedOutput);

                    unset($returnCode);
                } else {
                    $this->outputMessage('Cannot run command: ' . $commandMessage);
                    if ($commandNotFound) {
                        $this->outputMessage('Command does not exists.');
                    }
                }
                unset($input, $command, $commandNotFound, $commandMessage);
            }
        } catch (Exception $e) {
            throw $e;
        } finally {
            unset($container, $kernel, $application);
        }
    }

    /**
     * @param DoctrineRegistry $doctrine
     * @param bool $refreshArray
     * @return array|null
     * @throws LogicException
     * @author Fábio Mastelari <fabio.leite@arcasolutions.com>
     */
    private function getDomainDatabasesArray(&$doctrine, $refreshArray = false) {
        $domainDatabases = array();
        if($refreshArray || $this->domainDatabases === null || !is_array($this->domainDatabases)) {
            if (!empty($doctrine)) {
                /**
                 * @var DomainRepository $domainRepository
                 */
                $domainRepository = $doctrine->getRepository("CoreBundle:Domain", "main");
                if (!empty($domainRepository)) {
                    if ($this->allDomains) {
                        $activeDomains = $domainRepository->findBy(["status" => "A"]);
                        if (!empty($activeDomains)) {
                            /**
                             * @var Domain $activeDomain
                             */
                            foreach ($activeDomains as $activeDomain) {
                                $domainUrlValue = $activeDomain->getUrl();
                                $domainDatabaseValue = $activeDomain->getDatabaseName();
                                if (!empty($domainUrlValue) && !empty($domainDatabaseValue)) {
                                    $domainDatabases[$domainUrlValue] = $domainDatabaseValue;
                                }
                                unset($domainDatabaseValue, $domainUrlValue);
                            }
                        } else {
                            $noActiveDomainMessage = "No one domain with status A (Activated) has been found.";
                            $this->outputMessage($noActiveDomainMessage);

                            unset($noActiveDomainMessage);
                        }
                        unset($activeDomains);
                    } elseif (!empty($this->targetDomain)) {
                        /**
                         * @var Domain $activeDomain
                         */
                        $activeDomain = $domainRepository->findOneBy(["url" => $this->targetDomain]);
                        if (!empty($activeDomain)) {
                            $domainDatabaseValue = $activeDomain->getDatabaseName();
                            if (!empty($this->targetDomain) && !empty($domainDatabaseValue)) {
                                $domainDatabases[$this->targetDomain] = $domainDatabaseValue;
                            }
                            unset($domainDatabaseValue);
                        } else {
                            $noDomainWithUrlMessage = "No one domain with url equals to " . $this->targetDomain . " has been found.";
                            $this->outputMessage($noDomainWithUrlMessage);

                            unset($noDomainWithUrlMessage);
                        }
                    }
                } else {
                    unset($domainRepository);
                    throw new LogicException('The eDirectory domain repository cannot be retrieved.');
                }
                unset($domainRepository);
            } else {
                throw new LogicException('The Doctrine Registry cannot be retrieved as the doctrine instance is not yet set.');
            }
            $this->domainDatabases = empty($domainDatabases)?null:$domainDatabases;
        }
        return $this->domainDatabases;
    }

    protected $outputEchoMessageBuffer = '';
    private function outputMessage($message, $newline=true){
        if (!empty($this->io)) {
            if($newline===true) {
                $this->io->writeln($message);
            }
            else
            {
                $this->io->write($message);
            }
        } else {
            $this->outputEchoMessageBuffer .= $message;
            if($newline===true) {
                echo $this->outputEchoMessageBuffer;
                $this->outputEchoMessageBuffer = '';
            }
        }
    }

    /**
     * Execute required commands
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    private function executeAfterRequired(InputInterface $input)
    {
        if (!$input->getOption('execute-required')) {
            return;
        }

        /**
         * @var Application $application
         */
        $application = $this->getApplication();
        if (null === $application) {
            unset($application);
            throw new LogicException('The container cannot be retrieved as the application instance is not yet set.');
        }
        $environment = null;
        /**
         * @var Kernel $kernel
         */
        $kernel = $application->getKernel();
        if (!empty($kernel)) {
            /**
             * @var ContainerInterface $container
             */
            $container = $kernel->getContainer();
            if (!empty($container)) {
                // execute defined commands
                $requiredCommandsQueues = $this->getRequiredCommandsWorkflow();
                $individualBundleQueue = $requiredCommandsQueues['bundleIndividualCommands'];
                $singleCallQueue = $requiredCommandsQueues['singleCallCommands'];
                $bundlesWithException = [];
                foreach ($individualBundleQueue as $bundleName => $bundleCommands) {
                    try {
                        foreach ($bundleCommands as $command => $commandParams) {
                            $bundlesOfCommand = [$bundleName];
                            $processBuilder = null;
                            $arrayInputDict = null;
                            try {
                                switch ($command) {
                                    case 'migrate:main':
                                        $doctrine = $container->get("doctrine");
                                        if (!empty($doctrine)) {
                                            /**
                                             * @var DomainRepository $domainRepository
                                             */
                                            $this->runMigrationsOnMain($application, $doctrine, $bundlesOfCommand);
                                        } else {
                                            unset($doctrine);
                                            throw new LogicException('The Doctrine Registry cannot be retrieved as the doctrine instance is not yet set.');
                                        }
                                        break;
                                    case 'fixture:main':
                                        if (!empty($commandParams) && is_array($commandParams['fixtures'])) {
                                            /** @var array $arrayInputDict */
                                            $arrayInputDict = [
                                                'command' => 'doctrine:fixtures:load',
                                            ];

                                            foreach ($commandParams['fixtures'] as $fixture) {
                                                if(!array_key_exists('--fixtures',$arrayInputDict['--fixtures'])){
                                                    $arrayInputDict['--fixtures'] = array();
                                                }
                                                $arrayInputDictFixturesItemRef = &$arrayInputDict['--fixtures'];
                                                if(is_array($arrayInputDictFixturesItemRef)) {
                                                    $arrayInputDictFixturesItemRef[] = $fixture;
                                                }
                                            }
                                            $arrayInputDict['--append'] = true;
                                            $arrayInputDict['--em'] = 'main';

                                            $this->runApplicationCommand($arrayInputDict, $application);
                                            unset($arrayInputDict);
                                        }
                                        break;
                                    case 'migrate:domain':
                                    case 'fixture:domain':
                                        if (!empty($container)) {
                                            /**
                                             * @var DoctrineRegistry $doctrine
                                             */
                                            $doctrine = $container->get("doctrine");
                                            if (!empty($doctrine)) {
                                                $domainDatabases = $this->getDomainDatabasesArray($doctrine);

                                                /**
                                                 * @var Connection $domainConnection
                                                 */
                                                $domainConnection = $container->get('doctrine.dbal.domain_connection');
                                                if (!empty($domainConnection)) {
                                                    $domainConnectionParams = $domainConnection->getParams();
                                                    if (array_key_exists('dbname', $domainConnectionParams)) {
                                                        $originalDbName = $domainConnectionParams['dbname'];
                                                    }
                                                    if (!empty($domainDatabases)) {
                                                        foreach ($domainDatabases as $domainUrl => $domainDatabaseName) {
                                                            if (array_key_exists($bundleName, $this->skippedDomains) && in_array($domainUrl, $this->skippedDomains[$bundleName], true)) {
                                                                $skipDomainCommandDuePreviousExceptionMessage = "Command " . $command . " skipped on domain '" . $domainUrl . "' due to the previous exception.";
                                                                $this->outputMessage($skipDomainCommandDuePreviousExceptionMessage);
                                                                unset($skipDomainCommandDuePreviousExceptionMessage);
                                                            } else {
                                                                try {
                                                                    $dbNameExistsInConnectionParameters = array_key_exists('dbname', $domainConnectionParams);
                                                                    if (!$dbNameExistsInConnectionParameters || $domainDatabaseName != $domainConnectionParams['dbname']) {
                                                                        $domainConnectionParams['dbname'] = $domainDatabaseName;
                                                                        if ($domainConnection->isConnected()) {
                                                                            $domainConnection->close();
                                                                        }
                                                                        try {
                                                                            $domainConnection->__construct($domainConnectionParams, $domainConnection->getDriver(), $domainConnection->getConfiguration(), $domainConnection->getEventManager());
                                                                            $domainConnection->connect();
                                                                            $domainConnectionParams = $domainConnection->getParams();
                                                                        } catch (Exception $e) {
                                                                            throw $e;
                                                                        }
                                                                    }

                                                                    if ($command == 'migrate:domain') {
                                                                        $this->runMigrationsOnDomain($application, $doctrine, $bundlesOfCommand, $domainUrl);
                                                                    } else {
                                                                        switch ($command) {
                                                                            case 'fixture:domain':
                                                                                if (!empty($commandParams) && is_array($commandParams['fixtures'])) {
                                                                                    /** @var array $arrayInputDict */
                                                                                    $arrayInputDict = [
                                                                                        'command' => 'doctrine:fixtures:load',
                                                                                    ];

                                                                                    foreach ($commandParams['fixtures'] as $fixture) {
                                                                                        if (!array_key_exists('--fixtures', $arrayInputDict)) {
                                                                                            $arrayInputDict['--fixtures'] = array();
                                                                                        }
                                                                                        $arrayInputDict['--fixtures'][] = $fixture;
                                                                                    }
                                                                                    $arrayInputDict['--append'] = true;
                                                                                    $arrayInputDict['--em'] = 'domain';
                                                                                    $arrayInputDict['--domain'] = $domainUrl;
                                                                                    $this->runApplicationCommand($arrayInputDict, $application, $domainUrl);
                                                                                    unset($arrayInputDict);
                                                                                }
                                                                                break;
                                                                        }
                                                                    }
                                                                } catch (Exception $e) {
                                                                    if(!array_key_exists($bundleName,$this->skippedDomains)) {
                                                                        $this->skippedDomains[$bundleName] = [];
                                                                    }
                                                                    $this->skippedDomains[$bundleName][] = $domainUrl;

                                                                    $commandExceptionOnDomainMessage = "Unable to run command " . $command . " on domain '" . $domainUrl . "' due to the following exception. This domain will be skipped from now. Exception: " . $e->getMessage();
                                                                    $commandExceptionOnDomainTrace = "Exception trace: " . $e->getTraceAsString();

                                                                    $this->outputMessage($commandExceptionOnDomainMessage);
                                                                    $this->outputMessage($commandExceptionOnDomainTrace);

                                                                    unset($commandExceptionOnDomainMessage, $commandExceptionOnDomainTrace);
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $dbNameExistsInConnectionParameters = array_key_exists('dbname', $domainConnectionParams);
                                                    if (!empty($originalDbName) && (!$dbNameExistsInConnectionParameters || $originalDbName != $domainConnectionParams['dbname'])) {
                                                        $domainConnectionParams['dbname'] = $originalDbName;
                                                        if ($domainConnection->isConnected()) {
                                                            $domainConnection->close();
                                                        }
                                                        try {
                                                            $domainConnection->__construct($domainConnectionParams, $domainConnection->getDriver(), $domainConnection->getConfiguration(),
                                                                $domainConnection->getEventManager());
                                                            $domainConnection->connect();
                                                        } catch (Exception $e) {
                                                            throw $e;
                                                        }
                                                    }
                                                    unset($domainConnectionParams, $originalDbName);
                                                } else {
                                                    unset($domainConnection, $container, $kernel, $application);
                                                    throw new LogicException('The database driver connection related to domain can not be retrieved as the connection instance is not yet set.');
                                                }
                                                unset($domainDatabases, $domainConnection);

                                            } else {
                                                unset($doctrine);
                                                throw new LogicException('The Doctrine Registry cannot be retrieved as the doctrine instance is not yet set.');
                                            }
                                            unset($doctrine);
                                        }
                                        break;
                                }
                            } catch (Exception $e) {
                                unset($commandParams, $bundlesOfCommand, $processBuilder, $arrayInputDict, $container, $kernel, $application);
                                throw $e;
                            }
                        }
                        unset($bundlesOfCommand, $processBuilder, $arrayInputDict);
                    } catch (Exception $e) {
                        $exceptionDuringPluginInstallationMessage = "Error during execution of a installation command of plugin related to bundle '" . $bundleName . "' due to the following exception. This plugin further installation commands will be skipped from now. Exception: " . $e->getMessage();
                        $exceptionDuringPluginInstallationTrace = "Exception trace: " . $e->getTraceAsString();
                        if(!in_array($bundleName, $bundlesWithException)) {
                            $bundlesWithException[] = $bundleName;
                        }

                        $this->outputMessage($exceptionDuringPluginInstallationMessage);
                        $this->outputMessage($exceptionDuringPluginInstallationTrace);

                    }
                }
                $skipSyncCommands = false;
                foreach ($singleCallQueue as $command => $commandDefinition) {
                    $commandParams = $commandDefinition['params'];
                    $bundlesOfCommand = $commandDefinition['bundles'];
                    if(empty($bundlesOfCommand) || (count($bundlesOfCommand)==1 && in_array($bundlesOfCommand[0], $bundlesWithException)))
                    {
                        continue;
                    }
                    $processBuilder = null;
                    $arrayInputDict = null;
                    try {
                        switch ($command) {
                            case 'gulp-frontend':
                                $processBuilder = new ProcessBuilder();
                                $processBuilder->enableOutput()
                                    ->setTimeout(null)
                                    ->add('npm')
                                    ->add('run')
                                    ->add('gulp-frontend');
                                $this->runProcessBuilder($processBuilder);
                                unset($processBuilder);
                                break;
                            case 'assets:install':
                                $arrayInputDict = [
                                    'command' => 'assets:install',
                                    '--symlink' => true,
                                    '--relative' => true
                                ];
                                $this->runApplicationCommand($arrayInputDict, $application);
                                break;
                            case 'assetic:dump':
                                $arrayInputDict = [
                                    'command' => 'assetic:dump'
                                ];
                                $this->runApplicationCommand($arrayInputDict, $application);
                                break;
                            case 'edirectory:sync':
                            case 'elastic:recreate-index':
                                if (!empty($container)) {
                                    /**
                                     * @var DoctrineRegistry $doctrine
                                     */
                                    $doctrine = $container->get("doctrine");
                                    if (!empty($doctrine)) {
                                        $domainDatabases = $this->getDomainDatabasesArray($doctrine);
                                        /**
                                         * @var Connection $domainConnection
                                         */
                                        $domainConnection = $container->get('doctrine.dbal.domain_connection');
                                        if (!empty($domainConnection)) {
                                            $domainConnectionParams = $domainConnection->getParams();
                                            if (array_key_exists('dbname', $domainConnectionParams)) {
                                                $originalDbName = $domainConnectionParams['dbname'];
                                            }
                                            if (!empty($domainDatabases)) {
                                                foreach ($domainDatabases as $domainUrl => $domainDatabaseName) {
                                                    $skipDomain = false;
                                                    foreach($this->skippedDomains as $bundleName => $skippedDomain){
                                                        if(in_array($domainUrl, $skippedDomain, true)){
                                                            $skipDomain = true;
                                                        }
                                                    }
                                                    if ($skipDomain) {
                                                        $skipDomainCommandDuePreviousExceptionMessage = "Command " . $command . " skipped on domain '" . $domainUrl . "' due to the previous exception.";
                                                        $this->outputMessage($skipDomainCommandDuePreviousExceptionMessage);
                                                        unset($skipDomainCommandDuePreviousExceptionMessage);
                                                    } else {
                                                        try {
                                                            $dbNameExistsInConnectionParameters = array_key_exists('dbname', $domainConnectionParams);
                                                            if (!$dbNameExistsInConnectionParameters || $domainDatabaseName != $domainConnectionParams['dbname']) {
                                                                $domainConnectionParams['dbname'] = $domainDatabaseName;
                                                                if ($domainConnection->isConnected()) {
                                                                    $domainConnection->close();
                                                                }
                                                                try {
                                                                    $domainConnection->__construct($domainConnectionParams, $domainConnection->getDriver(), $domainConnection->getConfiguration(), $domainConnection->getEventManager());
                                                                    $domainConnection->connect();
                                                                    $domainConnectionParams = $domainConnection->getParams();
                                                                } catch (Exception $e) {
                                                                    throw $e;
                                                                }
                                                            }
                                                            switch ($command) {
                                                                case 'elastic:recreate-index':
                                                                    $this->outputMessage("Recreate Elasticsearch index.");
                                                                    /** @var Settings $multiDomainInformationService */
                                                                    $multiDomainInformationService = $container->get('multi_domain.information');
                                                                    $originalMultiDomainInformationActiveHost = $multiDomainInformationService->getActiveHost();
                                                                    $multiDomainInformationService->setActiveHost($domainUrl);
                                                                    $elasticSyncService = $container->get("elasticsearch.synchronization");
                                                                    if(!empty($elasticSyncService)) {
                                                                        if ($elasticSyncService->createIndex()) {
                                                                            $this->outputMessage("Index successfully recreated.");
                                                                        } else {
                                                                            $this->outputMessage("Index can not be recreated. Synchronization commands will be skipped from now.");
                                                                            $skipSyncCommands = true;
                                                                        }
                                                                        if(!empty($originalMultiDomainInformationActiveHost)) {
                                                                            $multiDomainInformationService->setActiveHost($originalMultiDomainInformationActiveHost);
                                                                        }
                                                                    } else {
                                                                        $this->outputMessage("Unable to get ElasticSearch synchronization service. Another synchronization commands will be skipped from now.");
                                                                        $skipSyncCommands = true;
                                                                    }
                                                                    break;

                                                                case 'edirectory:sync':
                                                                    if(!$skipSyncCommands) {
                                                                        $arrayInputDict = [
                                                                            'command' => 'edirectory:synchronize',
                                                                            '--recreate-index' => true
                                                                        ];
                                                                        $arrayInputDict['--domain'] = $domainUrl;
                                                                        $arrayInputDict['--force-domain'] = $domainUrl;
                                                                        $this->runApplicationCommand($arrayInputDict, $application, $domainUrl);
                                                                        unset($arrayInputDict);
                                                                    }
                                                                    break;
                                                            }

                                                        } catch (Exception $e) {
                                                            if(!array_key_exists($bundleName,$this->skippedDomains)) {
                                                                $this->skippedDomains[$bundleName] = [];
                                                            }
                                                            $this->skippedDomains[$bundleName][] = $domainUrl;

                                                            $commandExceptionOnDomainMessage = "Unable to run command " . $command . " on domain '" . $domainUrl . "' due to the following exception. This domain will be skipped from now. Exception: " . $e->getMessage();
                                                            $commandExceptionOnDomainTrace = "Exception trace: " . $e->getTraceAsString();

                                                            $this->outputMessage($commandExceptionOnDomainMessage);
                                                            $this->outputMessage($commandExceptionOnDomainTrace);

                                                            unset($commandExceptionOnDomainMessage, $commandExceptionOnDomainTrace);
                                                        }
                                                    }
                                                }
                                            }
                                            $dbNameExistsInConnectionParameters = array_key_exists('dbname', $domainConnectionParams);
                                            if (!empty($originalDbName) && (!$dbNameExistsInConnectionParameters || $originalDbName != $domainConnectionParams['dbname'])) {
                                                $domainConnectionParams['dbname'] = $originalDbName;
                                                if ($domainConnection->isConnected()) {
                                                    $domainConnection->close();
                                                }
                                                try {
                                                    $domainConnection->__construct($domainConnectionParams, $domainConnection->getDriver(), $domainConnection->getConfiguration(),
                                                        $domainConnection->getEventManager());
                                                    $domainConnection->connect();
                                                } catch (Exception $e) {
                                                    throw $e;
                                                }
                                            }
                                            unset($domainConnectionParams, $originalDbName);
                                        } else {
                                            unset($domainConnection, $container, $kernel, $application);
                                            throw new LogicException('The database driver connection related to domain can not be retrieved as the connection instance is not yet set.');
                                        }
                                        unset($domainDatabases, $domainConnection);
                                    } else {
                                        unset($doctrine);
                                        throw new LogicException('The Doctrine Registry cannot be retrieved as the doctrine instance is not yet set.');
                                    }
                                    unset($doctrine);
                                }
                                break;
                        }
                    } catch (Exception $e) {
                        unset($commandParams, $bundlesOfCommand, $processBuilder, $arrayInputDict, $container, $kernel, $application);
                        throw $e;
                    }
                }
                unset($bundlesOfCommand, $processBuilder, $arrayInputDict);
            } else {
                unset($container, $kernel, $application);
                throw new LogicException('The container cannot be retrieved as the container instance is not yet set.');
            }
            unset($container);
        } else {
            unset($kernel, $application);
            throw new LogicException('The kernel cannot be retrieved as the kernel instance is not yet set.');
        }
        unset($kernel, $application);
    }

    /**
     * Returns array of priority of sub-commands workflow
     *
     * @return array
     */
    private function getRequiredCommandsWorkflow()
    {
        $queue = [];

        // setup command list
        $afterCommandList = $this->requiredList['afterCommand'];

        $supportedSingleCallCommands = [
            'assets:install',
            'assetic:dump',
            'gulp-frontend',
            'elastic:recreate-index',
            'edirectory:sync'
        ];

        $supportedCommandsByBundle = [
            'migrate:main',
            'fixture:main',
            'migrate:domain',
            'fixture:domain'
        ];

        if (empty($queue)) {
            $queue = [
                'bundleIndividualCommands' => [],
                'singleCallCommands' => []
            ];
        }
        foreach($afterCommandList as $afterCommandBundleName => $afterCommands) {
            foreach ($afterCommands as $afterCommandName => $afterCommandParams) {
                if (in_array($afterCommandName, $supportedSingleCallCommands, true)) {
                    if(!array_key_exists($afterCommandName, $queue['singleCallCommands'])) {
                        $queue['singleCallCommands'][$afterCommandName] = [];
                        $queue['singleCallCommands'][$afterCommandName]['params'] = [];
                        $queue['singleCallCommands'][$afterCommandName]['bundles'] = [];
                    }
                    if(!in_array($afterCommandBundleName, $queue['singleCallCommands'][$afterCommandName]['bundles'], true)) {
                        $queue['singleCallCommands'][$afterCommandName]['bundles'][] = $afterCommandBundleName;
                    }
                    if($afterCommandParams!==null) {
                        // wrap params to an array if it is not a list of items
                        !is_array($afterCommandParams) and $afterCommandParams = [$afterCommandParams => null];
                        $queue['singleCallCommands'][$afterCommandName]['params'] = array_merge_recursive($queue['singleCallCommands'][$afterCommandName]['params'], $afterCommandParams);
                    }
                } else if (in_array($afterCommandName, $supportedCommandsByBundle, true)) {
                    if(!array_key_exists($afterCommandBundleName, $queue['bundleIndividualCommands'])) {
                        $queue['bundleIndividualCommands'][$afterCommandBundleName] = [];
                    }
                    if(!array_key_exists($afterCommandName, $queue['bundleIndividualCommands'][$afterCommandBundleName])) {
                        $queue['bundleIndividualCommands'][$afterCommandBundleName][$afterCommandName] = array();
                    }
                    if(!empty($afterCommandParams)) {
                        $queue['bundleIndividualCommands'][$afterCommandBundleName][$afterCommandName] = $afterCommandParams;
                    }
                }
            }
        }

        $supportedSingleCallCommandsOrder = array_flip($supportedSingleCallCommands);

        $orderCommandsFunction = static function($a, $b) use ($supportedSingleCallCommands, $supportedSingleCallCommandsOrder) {
            $returnValue = 1;
            $aIsSingleCallCommand = in_array($a, $supportedSingleCallCommands, true);
            $bIsSingleCallCommand = in_array($b, $supportedSingleCallCommands, true);

            if ($aIsSingleCallCommand && $bIsSingleCallCommand) {
                $returnValue = $supportedSingleCallCommandsOrder[$a] - $supportedSingleCallCommandsOrder[$b];
            } else if(!$aIsSingleCallCommand && !$bIsSingleCallCommand) { // A inválido, B inválido
                $returnValue = 0;
            } else if(!$aIsSingleCallCommand) { // A inválido, B válido
                $returnValue = -1;
            } else { // B inválido, A válido
                $returnValue = 1;
            }
            return $returnValue;
        };

        uksort($queue['singleCallCommands'], $orderCommandsFunction);
        unset($afterCommandList, $supportedCommands, $supportedSingleCallCommandsOrder, $orderCommandsFunction);

        return $queue;
    }

    /**
     * Runs migrations one by one on main database
     * @author Fábio Mastelari <fabio.leite@arcasolutions.com>
     *
     * @param Application $application
     * @param DoctrineRegistry $doctrine
     * @param $bundlesOfCommand
     * @throws Exception
     */
    protected function runMigrationsOnMain(&$application, &$doctrine, &$bundlesOfCommand){
        $this->runMigrationsOn($application, $doctrine,$bundlesOfCommand,'main');
    }

    /**
     * Runs migrations one by one on domain database
     * @param Application $application
     * @param DoctrineRegistry $doctrine
     * @param $bundlesOfCommand
     * @param string|null $domainUrl
     * @throws Exception
     * @author Fábio Mastelari <fabio.leite@arcasolutions.com>
     *
     */
    protected function runMigrationsOnDomain(&$application, &$doctrine, &$bundlesOfCommand, string $domainUrl=null){
        $this->runMigrationsOn($application, $doctrine,$bundlesOfCommand,'domain', $domainUrl);
    }

    /**
     * Runs migrations one by one on a destination database configuration
     * @param Application $application
     * @param DoctrineRegistry $doctrine
     * @param $bundlesOfCommand
     * @param $migrationDestination
     * @param string|null $domainUrl
     * @throws \Doctrine\DBAL\DBALException
     * @author Fábio Mastelari <fabio.leite@arcasolutions.com>
     *
     */
    protected function runMigrationsOn(&$application, &$doctrine, &$bundlesOfCommand, $migrationDestination, string $domainUrl=null){
        if (($migrationDestination=='main') || ($migrationDestination=='domain')) {
            $managerName = 'domain';
            $migrationFolderName = 'Domain';
            $configurationFileName = 'domain.yml';
            if ($migrationDestination=='main'){
                $managerName = 'main';
                $migrationFolderName = 'Main';
                $configurationFileName = 'main.yml';
            }

            if (!empty($doctrine)) {
                /**
                 * @var EntityManager
                 */
                $em = $doctrine->getManager($managerName);
                try {
                    if (!empty($em)) {
                        /**
                         * @var Connection $connection
                         */
                        $connection = $em->getConnection();
                        $migrationVersionQuery = 'SELECT version FROM migration_versions WHERE version = :version';

                        foreach ($bundlesOfCommand as $bundleOfCommand) {
                            $pluginName = str_replace('Bundle', '', $bundleOfCommand);
                            $migrationsOfPlugin = __DIR__ . '/../../../../app/DoctrineMigrations/' . $migrationFolderName . '/Version' . $pluginName . '*.php';
                            $migrationsOfPluginFiles = glob($migrationsOfPlugin, GLOB_BRACE);
                            if ($migrationsOfPluginFiles != null) {
                                try {
                                    foreach ($migrationsOfPluginFiles as $migrationFile) {
                                        $migrationName = preg_replace('/^.*\/Version(.+)\.php/', '$1', $migrationFile);
                                        $migrationVersionStatement = $connection->prepare($migrationVersionQuery);
                                        $migrationVersionStatement->bindValue('version', $migrationName);
                                        $migrationVersionStatement->execute();
                                        $migrationVersionCountFromDb = $migrationVersionStatement->rowCount();

                                        if ($migrationVersionCountFromDb == 0) {
                                            $arrayInputDict = [
                                                'command' => 'doctrine:migrations:execute',
                                                '--em' => $managerName,
                                                '--configuration' => 'app/config/migrations/' . $configurationFileName,
                                                'version' => $migrationName,
                                                '--up' => true,
                                                '--no-interaction' => true
                                            ];

                                            $this->runApplicationCommand($arrayInputDict, $application, $domainUrl);
                                            unset($arrayInputDict);
                                        }
                                        unset($migrationName, $migrationVersionStatement, $migrationVersionCountFromDb);
                                    }
                                } catch (Exception $e) {
                                    $exceptionMessage = "Error during execution of plugin '" . $pluginName . "' migrations.";
                                    $exceptionDetail = "Exception: " . $e->getMessage();
                                    $exceptionStackTrace = "Exception trace: " . $e->getTraceAsString();
                                    $this->outputMessage($exceptionMessage);
                                    $this->outputMessage($exceptionDetail);
                                    $this->outputMessage($exceptionStackTrace);

                                    unset($exceptionMessage, $exceptionDetail, $exceptionStackTrace);
                                }
                            }
                            unset($migrationsOfPluginFiles,$migrationsOfPlugin,$pluginName);
                        }
                        unset($connection, $migrationVersionQuery);
                    }
                } catch (Exception $e){
                    unset($em);
                    throw $e;
                }
                unset($em);
            }

            unset($managerName, $migrationFolderName, $configurationFileName);
        }
    }
}
