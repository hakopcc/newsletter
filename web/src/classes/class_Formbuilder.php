<?php

/**
 * @package         jquery.Formbuilder
 * @author                 Michael Botsko
 * @copyright         2009, 2012 Trellis Development, LLC
 *
 * This PHP object is the server-side component of the jquery formbuilder
 * plugin. The Formbuilder allows you to provide users with a way of
 * creating a formand saving that structure to the database.
 *
 * Using this class you can easily prepare the structure for storage,
 * rendering the xml file needed for the builder, or render the html of the form.
 *
 * This package is licensed using the Mozilla Public License 1.1
 *
 * We encourage comments and suggestion to be sent to mbotsko@trellisdev.com.
 * Please feel free to file issues at http://github.com/botskonet/jquery.formbuilder/issues
 * Please feel free to fork the project and provide patches back.
 */


/**
 * @abstract This class is the server-side component that handles interaction with
 * the jquery formbuilder plugin.
 * @package jquery.Formbuilder
 */
class Formbuilder
{

    public    $tabIndex;
    /**
     * @var array Holds the form id and array form structure
     * @access protected
     */
    protected $_form_array = [];

    /**
     * Constructor, loads either a pre-serialized form structure or an incoming POST form
     * @param bool $form
     * @param string $tabIndex
     */
    public function __construct($form = false, $tabIndex = "")
    {
        $form = is_array($form) ? $form : [];
        $this->tabIndex = $tabIndex;
        // Set the serialized structure if it's provided
        // otherwise, store the source
        if (array_key_exists('form_structure', $form)) {
            $form['form_structure'] = json_decode($form['form_structure'], true);
            $this->_form_array = $form;
        } else {
            if (array_key_exists('frmb', $form)) {
                $_form = [];
                $_form['form_id'] = ($form['form_id'] == "undefined" ? false : $form['form_id']);
                $_form['form_structure'] = $form['frmb']; // since the form is from POST, set it as the raw array
                $this->_form_array = $_form;
            }
        }

        if (is_array($this->_form_array['form_structure'])) {
            foreach ($this->_form_array['form_structure'] as &$structure) {
                $structure['values'] = $structure['values'] ?: [];
            }
        }
    }


    /**
     * Returns the form array with the structure encoded, for saving to a database or other store
     *
     * @access public
     * @return array
     */
    public function get_encoded_form_array()
    {
        return ['form_id'        => $this->_form_array['form_id'],
                'form_structure' => json_encode($this->_form_array['form_structure']),
        ];
    }


    /**
     * Prints out the generated json file with a content-type of application/json
     *
     * @access public
     */
    public function render_json()
    {
        header("Content-Type: application/json");
        print json_encode($this->_form_array);
    }


    /**
     * Renders the generated html of the form.
     *
     * @param string $form_action Action attribute of the form element.
     * @access public
     * @uses generate_html
     */
    public function render_html($form_action = false)
    {
        print $this->generate_html($form_action);
    }


    /**
     * Generates the form structure in html.
     *
     * @param string $form_action Action attribute of the form element.
     * @return string
     * @access public
     */
    public function generate_html($form_action = false)
    {

        $html = '';

        $form_action = $form_action ? $form_action : $_SERVER['PHP_SELF'];

        if (is_array($this->_form_array['form_structure'])) {

//                $html .= '<form class="frm-bldr" method="post" action="'.$form_action.'">' . "\n";
//                $html .= '<ol class="frmb">'."\n";
            $html .= '<div class="row-fluid custom-form">';

            foreach ($this->_form_array['form_structure'] as $field) {
                $html .= $this->loadField((array)$field);
                $this->tabIndex++;
            }

//                $html .= '<li class="btn-submit"><input type="submit" name="submit" value="Submit" /></li>' . "\n";
//                $html .=  '</ol>' . "\n";
//                $html .=  '</form>' . "\n";
            $html .= '</div>';

        }

        return $html;

    }

    /**
     * Loads a new field based on its type
     *
     * @param array $field
     * @access protected
     * @return string
     */
    protected function loadField($field)
    {

        if (is_array($field) && isset($field['cssClass'])) {

            switch ($field['cssClass']) {

                case 'input_text':
                    return $this->loadInputText($field);
                    break;
                case 'textarea':
                    return $this->loadTextarea($field);
                    break;
                case 'checkbox':
                    return $this->loadCheckboxGroup($field);
                    break;
                case 'radio':
                    return $this->loadRadioGroup($field);
                    break;
                case 'select':
                    return $this->loadSelectBox($field);
                    break;
            }
        }

        return false;

    }

    /**
     * Returns html for an input type="text"
     *
     * @param array $field Field values from database
     * @access protected
     * @return string
     */
    protected function loadInputText($field)
    {

        $field['required'] = $field['required'] == 'checked' || $field['required'] == 'true' ? ' required' : false;

        $html = '';
        $html .= sprintf('<div class="%s%s" id="fld-%s">'."\n", $this->elemId($field['cssClass']), $field['required'],
            $this->elemId($field['values']));
        $html .= sprintf('<label for="%s">%s'.($field['required'] ? ' <b>*</b>' : '').'</label>'."\n",
            $this->elemId($field['values']), $field['values']);
        $html .= sprintf('<input type="text" class="text span12" id="%s" name="%s" value="%s" tabindex="'.$this->tabIndex.'" />'."\n",
            $this->elemId($field['values']),
            $this->elemId($field['values']),
            $this->getPostValue($this->elemId($field['values'])));
        $html .= '</div>'."\n";

        return $html;

    }

    //+++++++++++++++++++++++++++++++++++++++++++++++++
    // NON-PUBLIC FUNCTIONS
    //+++++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * Generates an html-safe element id using it's label
     *
     * @param string $label
     * @return string
     * @access protected
     */
    protected function elemId($label, $prepend = false)
    {
        if (is_string($label)) {
            $prepend = is_string($prepend) ? $this->elemId($prepend).'-' : false;

            return $prepend.strtolower(preg_replace("/[^A-Za-z0-9_]/", "", str_replace(" ", "_", $label)));
        }

        return false;
    }

    /**
     * Attempts to load the POST value into the field if it's set (errors)
     *
     * @param string $key
     * @return mixed
     */
    protected function getPostValue($key)
    {
        return array_key_exists($key, $_POST) ? $_POST[$key] : false;
    }

    /**
     * Returns html for a <textarea>
     *
     * @param array $field Field values from database
     * @access protected
     * @return string
     */
    protected function loadTextarea($field)
    {

        $field['required'] = $field['required'] == 'checked' || $field['required'] == 'true' ? ' required' : false;

        $html = '';
        $html .= sprintf('<div class="%s%s" id="fld-%s">'."\n", $this->elemId($field['cssClass']), $field['required'],
            $this->elemId($field['values']));
        $html .= sprintf('<label for="%s">%s'.($field['required'] ? ' <b>*</b>' : '').'</label>'."\n",
            $this->elemId($field['values']), $field['values']);
        $html .= sprintf('<textarea class="textarea span12" id="%s" name="%s" rows="5" cols="50" tabindex="'.$this->tabIndex.'">%s</textarea>'."\n",
            $this->elemId($field['values']),
            $this->elemId($field['values']),
            $this->getPostValue($this->elemId($field['values'])));
        $html .= '</div>'."\n";

        return $html;

    }


    /**
     * Returns html for an <input type="checkbox"
     *
     * @param array $field Field values from database
     * @access protected
     * @return string
     */
    protected function loadCheckboxGroup($field)
    {

        $field['required'] = $field['required'] == 'checked' || $field['required'] == 'true' ? ' required' : false;

        $html = '';
        $html .= sprintf('<div class="%s%s" id="fld-%s">'."\n", $this->elemId($field['cssClass']), $field['required'],
            $this->elemId($field['title']));

        if (isset($field['title']) && !empty($field['title'])) {
            $html .= sprintf('<label class="false_label">%s'.($field['required'] ? ' <b>*</b>' : '').'</label>'."\n",
                $field['title']);
        }
        $field['values'] = (array)$field['values'];
        if (isset($field['values']) && is_array($field['values'])) {
            $html .= sprintf('<span class="multi-row clearfix">')."\n";
            foreach ($field['values'] as $item) {

                $item = (array)$item;

                // set the default checked value
                $checked = $item['default'] == 'true' ? true : false;

                // load post value
                $val = $this->getPostValue($this->elemId($item['value'], $field['title']));
                $checked = !empty($val);

                // if checked, set html
                $checked = $checked ? ' checked="checked"' : '';

                $html .= '<input type="hidden" name="checkboxes[]" value="'.$this->elemId($item['value']).'">';
                $checkbox = '<span class="row clearfix"><input type="checkbox" id="%s-%s" name="%s-%s" value="%s"%s tabindex="'.$this->tabIndex.'" /><label for="%s-%s">%s</label></span>'."\n";
                $html .= sprintf($checkbox, $this->elemId($field['title']), $this->elemId($item['value']),
                    $this->elemId($field['title']), $this->elemId($item['value']), $item['value'], $checked,
                    $this->elemId($field['title']), $this->elemId($item['value']), $item['value']);
                $this->tabIndex++;
            }
            $html .= sprintf('</span>')."\n";
        }

        $html .= '</div>'."\n";

        return $html;

    }


    /**
     * Returns html for an <input type="radio"
     * @param array $field Field values from database
     * @access protected
     * @return string
     */
    protected function loadRadioGroup($field)
    {

        $field['required'] = $field['required'] == 'checked' || $field['required'] == 'true' ? ' required' : false;

        $html = '';

        $html .= sprintf('<div class="%s%s" id="fld-%s">'."\n", $this->elemId($field['cssClass']), $field['required'],
            $this->elemId($field['title']));

        if (isset($field['title']) && !empty($field['title'])) {
            $html .= sprintf('<label class="false_label">%s'.($field['required'] ? ' <b>*</b>' : '').'</label>'."\n",
                $field['title']);
        }
        $field['values'] = (array)$field['values'];
        if (isset($field['values']) && is_array($field['values'])) {
            $html .= sprintf('<span class="multi-row">')."\n";
            foreach ($field['values'] as $item) {

                $item = (array)$item;

                // set the default checked value
                $checked = $item['default'] == 'true' ? true : false;

                // load post value
                $val = $this->getPostValue($this->elemId($field['title']));
                $checked = $val == $item['value'];

                // if checked, set html
                $checked = $checked ? ' checked="checked"' : '';

                $radio = '<span class="row clearfix"><input type="radio" id="%s-%s" name="%1$s" value="%s"%s tabindex="'.$this->tabIndex.'" /><label for="%1$s-%2$s">%3$s</label></span>'."\n";
                $html .= sprintf($radio,
                    $this->elemId($field['title']),
                    $this->elemId($item['value']),
                    $item['value'],
                    $checked);
                $this->tabIndex++;
            }
            $html .= sprintf('</span>')."\n";
        }

        $html .= '</div>'."\n";

        return $html;

    }


    /**
     * Returns html for a <select>
     *
     * @param array $field Field values from database
     * @access protected
     * @return string
     */
    protected function loadSelectBox($field)
    {

        $field['required'] = $field['required'] == 'checked' || $field['required'] == 'true' ? ' required' : false;

        $html = '';

        $html .= sprintf('<div class="%s%s" id="fld-%s">'."\n", $this->elemId($field['cssClass']), $field['required'],
            $this->elemId($field['title']));

        if (isset($field['title']) && !empty($field['title'])) {
            $html .= sprintf('<label for="%s">%s'.($field['required'] ? ' <b>*</b>' : '').'</label>'."\n",
                $this->elemId($field['title']), $field['title']);
        }
        $field['values'] = (array)$field['values'];
        if (isset($field['values']) && is_array($field['values'])) {
            $multiple = $field['multiple'] == "true" || $field['multiple'] == "checked" ? ' multiple="multiple"' : '';
            $html .= sprintf('<select name="%s" id="%s"%s tabindex="'.$this->tabIndex.'">'."\n",
                $this->elemId($field['title']), $this->elemId($field['title']), $multiple);
            $html .= '<option value="">---</option>';

            foreach ($field['values'] as $item) {

                $item = (array)$item;

                // set the default checked value
                $checked = $item['default'] == 'true' ? true : false;

                // load post value
                $val = $this->getPostValue($this->elemId($field['title']));
                $checked = $val == $item['value'];

                // if checked, set html
                $checked = $checked ? ' selected="selected"' : '';

                $option = '<option value="%s"%s>%s</option>'."\n";
                $html .= sprintf($option, $item['value'], $checked, $item['value']);
            }

            $html .= '</select>'."\n";
            $html .= '</div>'."\n";

        }

        return $html;

    }

    /**
     * Parses the POST data for the results of the speific form values. Checks
     * for required fields and returns an array of any errors.
     *
     * @access public
     * @returns array
     */
    public function process()
    {

        $error = [];
        $results = [];
        $checkboxes = [];

        // Put together an array of all expected indices
        if (is_array($this->_form_array['form_structure'])) {
            foreach ($this->_form_array['form_structure'] as $field) {

                $field = (array)$field;

                $field['required'] = ($field['required'] == 'checked' || $field['required'] == 'true');

                if ($field['cssClass'] == 'input_text' || $field['cssClass'] == 'textarea') {

                    $val = $this->getPostValue($this->elemId($field['values']));

                    if ($field['required'] && empty($val)) {
                        $error[] = $field['values'];
                    } else {
                        $results[$this->elemId($field['values'])] = $val;
                    }
                } elseif ($field['cssClass'] == 'radio' || $field['cssClass'] == 'select') {

                    $val = $this->getPostValue($this->elemId($field['title']));

                    if ($field['required'] && empty($val)) {
                        $error[] = $field['title'];
                    } else {
                        $results[$this->elemId($field['title'])] = $val;
                    }
                } elseif ($field['cssClass'] == 'checkbox') {
                    $field['values'] = (array)$field['values'];
                    if (is_array($field['values']) && !empty($field['values'])) {

                        $at_least_one_checked = false;

                        foreach ($field['values'] as $item) {
                            $item = (array)$item;
                            $elem_id = $this->elemId($item['value'], $field['title']);

                            $val = $this->getPostValue($elem_id);

                            if (!empty($val)) {
                                $at_least_one_checked = true;
                            }

                            $results[$this->elemId($item['value'])] = $this->getPostValue($elem_id);
                            $checkboxes[$this->elemId($field['title'])][] = $this->elemId($item['value']);
                        }

                        if (!$at_least_one_checked && $field['required']) {
                            $error[] = $field['title'];
                        }
                    }
                } else {
                }
            }
        }

        $success = empty($error);

        return ['success' => $success, 'results' => $results, 'errors' => $error, 'checkboxes' => $checkboxes];

    }

    /**
     * Check from which group a checkbox belongs
     *
     * @access public
     * @returns string
     */
    public function getCheckboxGroup($option, $arrayGroups)
    {

        if (is_array($arrayGroups)) {

            foreach ($arrayGroups as $key => $group) {
                if (in_array($option, $group)) {
                    return $key;
                }
            }
        }

        return;
    }
}

?>