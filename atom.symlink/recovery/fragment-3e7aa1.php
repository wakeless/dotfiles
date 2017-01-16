<?php

require "lib/lib_autolink.php";
use Rex\Cache;
use Rex\OptGroup;
use Rex\Option;

use Icon\Help as HelpIcon;

class BaseFragment {
  /**
   * @var App
   */
	var $App;

  use Translate;

	function __construct($app) {
		$this->App = $app;
	}

  function validate($required) {
    return static::validate_data($required, $this->data);
  }

  static function validate_data($required, $data) {
    foreach(array_keys_or_values($required) as $index => $field) {
      $field_type = (isset($required[$field])) ? $required[$field] : null;

      if(!isset($data[$field]) || ($field_type && !($data[$field] instanceof $field_type))) {
        if($field_type) {
          $message = "{$field} must be passed into this fragment and must be an instance of {$field_type} instead it is ".get_class($data[$field]);
        } else {
          $message = "{$field} must be passed into this fragment.";
        }

        throw new FragmentInterfaceException($message);
      }
    }

    return true;
  }

  static function phoneFormat($number) {
    if(!$number) return "";

    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    try {
      $parsed_number = $phoneUtil->parse($number, Site()["CountryAbbr"]);
    } catch (\libphonenumber\NumberParseException $e) {
      return $number;
    }

    if(strtoupper(Site()["CountryAbbr"]) == $phoneUtil->getRegionCodeForNumber($parsed_number)) {
      $format = \libphonenumber\PhoneNumberFormat::NATIONAL;
    } else {
      $format = \libphonenumber\PhoneNumberFormat::INTERNATIONAL;
    }

    return $phoneUtil->format($parsed_number, $format);
  }

	static function currencyFormat($number, $currency = null) {
    if($currency instanceof Currency) $currency = $currency["Value"];

		$formatted = static::tag("span", ["class" => "money-amount"], @number_format($number, 2));
		if($currency) {
			$formatted = static::tag("span", ["class" => "money-currency"], $currency) . $formatted;
		}
		return $formatted;
	}

  static function currency_symbol($currency) {
    return (new \Services\CurrencyTranslator)->currency_symbol($currency);
  }

  static function tax_name($jurisdication) {
    return (new \Services\Translator\TaxJurisdiction)->tax_name($jurisdication);
  }

  static function company_number_name($jurisdication) {
    return (new \Services\Translator\TaxJurisdiction)->company_number_name($jurisdication);
  }

  static function getOptionForObject($object, $label_key, $value_key) {
    $options = [];
    foreach($object as $item) {
      $options[] = new Rex\Option($item[$label_key], $item[$value_key]);
    }

    return $options;
  }

  static function getOptionForArray($array) {
    $options = [];
    foreach($array as $label => $value) {
      $options[] = new Rex\Option($value, $label);
    }

    return $options;
  }

  static function to_float($int) {
    return number_format($int, 2, ".", "");
  }

  static function format_text_to_html($text, $linkify = false, $check_html = false) {
    if(!$check_html || stripos($text, "<p>") === false) {
      $text = nl2br($text);
    }

    if($linkify) $text = autolink($text);
    return $text;
  }

	static function dom_id($object, $lower = true) {
	  $dom_id = is_object($object) ? get_class($object)."_".$object["ID"] : "";

    if($lower) $dom_id = strtolower($dom_id);
    return $dom_id;
	}

  static function truncate_string($string, $length = 40) {
    if(strlen($string) > $length) {
      $string = substr($string, 0, $length) . '...';
    }

    return $string;
  }

	static function to_sentence($result, $attribute = "Name") {

		$items = array();
		foreach($result as $item) {
			if(is_string($item)) {
				$items[] = $item;
			} else {
			  $items[] = $item[$attribute];
			}
		}
		$items[] = implode(" and ", array_splice($items, -2));

		return implode(", ", $items);
	}

  static function camel_to_snake_case($str) {
    return strtolower(self::to_snake_case(self::from_camel_case($str)));
  }

  static function from_camel_case($str) {
    $str = preg_replace("/([A-Z\d]+)([A-Z][a-z])/",'\1 \2', $str);
    $str = preg_replace("/([a-z\d])([A-Z])/", '\1 \2', $str);
    return trim($str);
  }

  static function to_snake_case($str) {
    return preg_replace("/\s+/", "_", $str);
  }

  static function from_snake_case($str) {
    $str = preg_replace("/_/", " ", $str);
    return $str;

  }

	static function pluralise($num, $subject) {
		if($num == 1) {
			return $subject;
		} else {
			return $subject."s";
		}
	}

	static function xmlEscape($text) {
	  if(mb_detect_encoding($text) != "UTF-8") {
  		$text = mb_convert_encoding($text, 'UTF-8');
	  }

    return self::htmlescape($text);
	}

	static function htmlescape($value) {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8", false);
	}

	static function dateFormat($date, $format= "d/m/Y") {
		return Rex\Helper\DateHelper::format($format, $date);
	}

  static function shortDateFormat($date) {
    $format = Site()->shortDateFormat();

		return Rex\Helper\DateHelper::format($format, $date);
  }

  static function shortTimeFormat($date) {
    return self::shortDateFormat($date)." ".$date->format("H:i");
  }

  static function longDateFormat($date) {
    $format = Site()->longDateFormat();

		return Rex\Helper\DateHelper::format($format, $date);
  }


  static function boolFormat($data, $extra = []) {
    if($data) {
      return new \Icon\Success($extra);
    } else {
      return new \Icon\Remove(["class" => "danger"] + $extra);
    }
  }

  static function icon($class, $library="glyphicon", $extra_class='') {
    return "<i class='$library $library-$class $extra_class'></i>";
  }

  static function entity_register_url($entity) {
    if($entity instanceof Event) {
      return 'register/event/'.$entity["ID"];
    } elseif($entity instanceof Organisation) {
      return 'register/organisation/'.$entity["ID"];
    } else {
      throw new IncorrectEntityTypeException("Entity musy be an instace of Event or Organisation.");
    }
  }

  static function link_to($text, $url, $extra = array()) {
    if(is_rex_null($url)) return $text;

    $extras =  self::attrs(["href" => evalThunk($url)] + $extra);

    return "<a$extras>$text</a>";
  }

	public function here() {
		return $this->App->here();
	}

	public function action() {
		return $this->App->action();
	}

	function link($action, $get = null) {
    return $this->App->link($action, $get);
	}

	function url($get = null) {
		return $this->link("", $get);
	}

  function __call($name, $args) {
    return call_user_func_array([$this->App, $name], $args);
  }

  static function buffer($func) {
    if($func instanceof \Closure) {
      ob_start();
      try {
        call_user_func($func);
      } catch(Exception $e) {
        ob_end_clean();
        throw $e;
      }
      return ob_get_clean();
    } else {
      return $func;
    }
  }

  static function outputBuffer($output) {
    if($output instanceof \Closure) {
      return $output();
    } else {
      return $output;
    }
  }


  function isFragment($fragment) {
    return $this->App->isFragment($fragment);
  }

  function renderPartial($fragment, $data = array(), $return = false) {
    $this->renderFragment($fragment, $data, $return);
  }

  function renderFragment($fragment, $data = array(), $return = false) {
    return $this->App->renderPartial($fragment, $data, $return);
  }

  static function is_checkable($type) {
    return \Form2::is_checkable($type);
  }

  static function is_checkable_list($type) {
    return \Form2::is_checkable_list($type);
  }

  static function is_selectable($type) {
    return \Form2::is_selectable($type);
  }

  static function attrs($attrs, $ignore=array()) {
    if(is_string($attrs)) return $attrs;

    $output = "";
    foreach($ignore as $i) {
      unset($attrs[$i]);
    }

    if($attrs) foreach($attrs as $key => $value) {
      if(in_array($key, array("checked")) && !$value) {
      } elseif($key == "data" && is_array($value)) {
        foreach($value as $k => $v) {
          $output .= " data-{$k}='".self::htmlescape($v)."' ";
        }
      } else {
        if(is_array($value)) $value = implode(",", $value);

        if($key != "value" && $value === true) {
          $output .= " $key='$key'";
        } elseif($key != "value" && $value === false) {
          //do nothing
        } else {
          $output .= " $key='".self::htmlescape($value)."' ";
        }

      }
    }
    return $output;
  }

  function getTPath() {
    return array_merge(arrayify(trim($this->t_path, ".")),  arrayify($this->App->getTPath()));
  }

  static function hidden($name, $value, $extras= []) {
    $output = '';
    $value = $value ?: @$extras["default"];
    return $output . self::input($name, $value, "hidden", $extras);
  }

  static function h3($name, $value, $extras = []) {
    $value = !is_null($value) ? $value : @$extras["value"];
    return self::tag("h3", array("class" => $name), $value);
  }

  static function p($name, $value, $extras = []) {
    $value = !is_null($value) ? $value : @$extras["value"];
    return self::tag("p", array("class" => $name), $value);
  }

  static function h5($name, $value, $extras = []) {
    $value = !is_null($value) ? $value : @$extras["value"];
    return self::tag("h5", array("class" => $name), $value);
  }

  static function label($name, $value, $extras = []) {
    $value = !is_null($value) ? $value : @$extras["value"];
    return self::tag("label", $extras, $value);
  }

  static function h4($name, $value, $extras = []) {
    $value = !is_null($value) ? $value : @$extras["value"];
    return self::tag("h4", array("class" => $name), $value);
  }

  static function input($name, $value, $type, $extras=[]) {
    if(!$extras) $extras = [];

    $name = isset($extras["name"]) ? $extras["name"] : $name;
    $id = self::parseID(isset($extras["id"]) ? $extras["id"] : $name);

    $value = !is_null($value) ? $value : @$extras["value"];

    return "<input ".self::attrs(["name" => $name, "type" => $type, "id" => $id, "value" => $value]).self::attrs($extras, array("name", "type", "id", "value"))." />";
  }

  static function map($name, $value) {
    $uri = $value->escapedURI();
    $width = getimagesize($uri)[0];
    $height = getimagesize($uri)[1];
    return "<div class='image-map' style='background: url(\"{$uri}\"); background-size: {$width}px {$height}px; width: {$width}px; height: {$height}px;'></div>";
  }

  static function panel($text, $name='', $heading='', $type='', $options=[]) {
    $classes = (isset($options["class"])) ? $options["class"] : "";
    $output = "<div id='{$name}-help' class='{$classes} panel {$type}'>";
      if($heading) $output .= "<div class='panel-heading'>{$heading}</div>";
      $output .= "<div class='panel-body'>{$text}</div>";
    $output .= "</div>";

    return $output;
  }

  static function validBootstrapClasses() {
    return [
      "default",
      "primary",
      "success",
      "info",
      "warning",
      "danger",
      "important",
    ];
  }

  static function isValidBootstrapClass($class) {
    return in_array($class, self::validBootstrapClasses());
  }

  static function badge($class='success', $content) {
    if(self::isValidBootstrapClass($class)) $class = "badge-{$class}";

    $content = self::buffer($content);
    return "<span class='badge {$class}'>{$content}</span>";
  }

  static function div($attributes= [], $contents=null, $close=false, $help=false) {
    return self::tag("div", $attributes, $contents, $close, $help);
  }

  static function option($attributes= [], $selected_value=null, $contents=null, $value=null) {
    if($selected_value == $value) $attributes["selected"] = "selected";
    $attributes["value"] = $value;
    return self::tag("option", $attributes, $contents);
  }

  static function tag($type, $attributes= [], $contents=null, $close=false) {

    if(isset($attributes["data"])) {
      foreach($attributes["data"] as $key => $value) {
        $attributes["data-$key"] = $value;
      }
      unset($attributes["data"]);
    }

    if($contents instanceof \Closure) {
      $contents = $contents();
    }
    if($contents instanceof \Rex\Data\Result) $contents = $contents->getArrayCopy();
    if(is_array($contents)) $contents = implode("", $contents);

    $output = "<$type ".self::attrs($attributes);
    if(is_null($contents) && !$close) {
      $output .= " />";
    } elseif(is_array($contents)) {
      $output .= ">".implode("", $contents)."</$type>";
    } else {
      $output .= ">$contents</$type>";
    }

    return $output;
  }

  static function options($values, $selected=null, $numKeys = false) {
    $output = "";
    $useVal = false;

    if ((range(0,count($values)-1) === @array_keys($values)) && !$numKeys) $useVal = true;
    foreach ($values as $key => $value){
      if($value instanceof OptGroup) {
        $output .= $value->output($selected);
      } elseif($value instanceof Rex\Option) {
        $output .= $value->output($selected);
      } else {
        $index = $useVal ? $value : $key;

        $attributes = array("value" => $index);

        if ((is_rex_array($selected) && in_rex_array($index, $selected)) || @(!is_null($selected) && (string)$selected === (string)$index)) {
          $attributes["selected"] = "selected";
        }

        $output .= self::tag("option", $attributes, $value);
      }
    }
    return $output;

  }

  static function parseID($id) {
    return FormHelper::parseID($id);
  }

  static function parseClass($class) {
    return FormHelper::parseClass($class);
  }


  static function submit($name, $value, $extras = []) {
    $value = $value ? $value : ucfirst($name);
    $bootstrap_class = @$extras["bootstrap"] ? $extras["bootstrap"] : "btn-primary";
    $class = " btn {$bootstrap_class}";

    $extras["class"] = @$extras["class"] . $class;

    return self::tag("input",
      ["type" => "submit", "name" => $name, "value" => $value] + $extras + ["id" => self::to_snake_case($name)],
      "", true);
  }

  static function button($name, $value, $extras = []) {
    return self::input($name, $value, "button", $extras);
  }

  static function cancel($name, $value, $extras = []) {
    return self::button($name, $value, ["class" => "btn btn-default cancel ".@$extras["class"]] + $extras);
  }

  static function a($name, $value, $extras = []) {
    return self::tag("a", $extras, $value);
  }

  static function icon_checkbox($name, $value, $extras=[]) {
    $extras["null"] = 0;

    if(!isset($extras["icon"])) $extras["icon"] = "tick";

    $out = self::checkbox($name, $value, $extras);
    $out .= self::icon($extras["icon"]);

    return $out;
  }

  static function checkbox($name, $value, $extras=[]) {
    if(!isset($extras["value"])) {
      $extras["value"] = 1;
    }

    $checked = is_rex_array($value) && (in_rex_array($extras["value"], $value) || isset($value[$extras["value"]]));
    if($checked || (@($extras["value"] == $value) && !is_rex_null($value))) $extras["checked"] = "checked";

    return self::buffer(function() use($extras, $name) { ?>
      <?php if(!isset($extras["null"]) || (isset($extras["null"]) && !$extras["null"])): ?>
        <?= self::hidden($name, "", array("id" => "")); ?>
      <?php endif; ?>

      <?= self::input($name, $extras["value"], "checkbox", $extras); ?>
    <?php });
  }

  static public function checkList($name, $value, $extras = []) {

    if (isset($extras["name"]) && $extras["name"]) {
      $name = $extras["name"];
    }

    $class = (isset($extras["class"])) ? $extras["class"] : "";

    $output = self::hidden($name, "");

    foreach($extras["options"] as $key => $label) {
      $output .= "<div class='checkbox'><label>";

      $checked = is_array($value) && (in_array($key, $value) || isset($value[$key]));

      $output .= self::checkBox($name."[]", "", [
        "value" => $key,
        "checked" => $checked,
        "id" => $name."[".$key."]",
        "null" => true,
      ]);

      $output .= " $label</label></div>";
    }

    return $output;
  }

  static public function nestedCheckList($name, $value, $extras = []) {
    // requires the format array("Object" => array(ObjectClass => array(Nested Items)));
    // If there are no nested items, the array is replaced by the ObjectClass

    $error = false;

    if (isset($extras["name"]) && $extras["name"]) {
      $name = $extras["name"];
    }

    $output = "<fieldset class='nestedCheckList'><ol>";

    if(count($extras["options"]) == 1) {
      foreach($extras["options"] as $type => $children) {
        if(is_string($type) && is_array($children)) {
          foreach($children as $child) {
            if(is_object($child) || (isset($child["ID"]) && isset($child["Name"]))) {
              $object = $child;
              $nest = null;
            } else {
              $object = $child[0];
              $nest = $child[1];
            }

            $label = $object["Name"];

            $output .= "<li>";

            if(isset($value[$type]) && in_array($object["ID"], $value[$type])) {
              $checked = 1;
            } else {
              $checked = 0;
            }

            $for = self::parseID($name."[".$type."[".$object["ID"]."]]");

            $output .= self::checkBox($name.'['.$type."][]", "", array("class" => $type, "value" => $object["ID"], "checked" => $checked, "id" => $name."[".$type."[".$object["ID"]."]]", "null" => true));
            $output .= "<label for=$for>$label</label>";

            if($nest) {
              $output .= self::nestedCheckList($name, $value, array("options" => $nest));
            }
            $output .= "</li>";
          }
        } else {
          $error = true;
        }
      }
    } else {
      $error = true;
    }


    if($error){
      user_error(INVALID_NESTED_CHECKLIST_FORMAT, E_USER_ERROR);
    }

    $output .= "</ol></fieldset>";

    return $output;
  }


  static function typeahead($name, $value, $extras = []) {
    if(!isset($extras["class"])) {
      $extras["class"] = "";
    }
    $extras["class"] .= " typeahead ";
    return self::text($name."_Typeahead", $extras["text"], $extras).
           self::hidden($name, $value);
  }

  function user_typeahead($name, $value, $extras = []) {
    $extras["attributes"] = [
      "prepend-icon" => "glyphicon glyphicon-user",
    ];
    $extras["placeholder"] =  (isset($extras["placeholder"])) ? $extras["placeholder"] : $this->t("form.base.usersearch_placeholder");

    if(!isset($extras["text"])) $extras["text"] = "";

    return self::typeahead($name, $value, $extras);
  }

  function usersearch($name, $value, $extras = []) {
    return self::user_typeahead($name, $value, $extras);
  }

  static function selecttext($key, $value, $extras=[]) {
    $default = @$extras["default"] ?: "";
    return self::select($key, $value, array('options' => $extras['options'], 'id' => $key.'Select')).
      self::text($key, $value, array('id' => $key.'Text', "value" => $default));
  }

  static function dateTimezone($name, $value, $extras=[]) {
    $output = "<div class='date-timezone form-inline'>";

      $output .= self::datetime($name, $value, $extras);
      $output .= "<div class='form-group'>";
      $output .= self::timezone($name."[Timezone]", !is_rex_null($value) ? $value->getTimezone()->getName() : _DEFAULT_TIMEZONE);
      $output .= "</div>";
    $output .= "</div>";

    return $output;
  }

  static function datetime($name, $value, $extras=[]) {
    $output = "";

      $output .= "<div class='form-group'>";
    $output .= self::date($name."[Date]", $value, $extras);
      $output .= "</div>";
      $output .= "<div class='form-group'>";
    $output .= self::time($name."[Time]", $value, $extras);
      $output .= "</div>";

    return $output;
  }

  static function timezone($name, $value, $extras = []) {
    $output = self::select($name, $value ?: _DEFAULT_TIMEZONE, ["options" => TimezoneHandler::TimeZoneList(), "class" => @$extras["class"].=' chosen']);
    return $output;
  }

  static function time($name, $value, $extras=[]) {
    if($value instanceof DateTimeInterface) {
      $value = $value->format('H:i');
      echo $value;
      die("ASDFASDF");
    }
    return self::text($name, $value, ["attributes" => ["prepend-icon" => 'glyphicon glyphicon-time'], "placeholder" => "HH/MM eg 15:30"]);
  }

  static function date($name, $value, $extras=[]) {
    if($value instanceof DateTimeInterface) $value = $value->format('d/m/Y');
    if(isset($extras["start_date"])) $extras["data-date-startdate"] = $extras["start_date"]->format("d/m/Y");
    if(isset($extras["end_date"])) $extras["data-date-enddate"] = $extras["end_date"]->format("d/m/Y");
    if(isset($extras["default_date"])) $extras["data-date-defaultdate"] = $extras["default_date"]->format("d/m/Y");
    $extras["data-date-format"] = "DD/MM/YYYY";
    $extras["class"] = (isset($extras["class"])) ? "{$extras["class"]} datepicker" : "datepicker";
    $extras = $extras ?: [];

    return self::text($name, $value, $extras + ['data-date' => $value, "attributes" => ["prepend-icon" => 'glyphicon glyphicon-calendar'], "placeholder" => "dd/mm/yyyy"]);
  }

  function email($name, $value, $extras = []) {
    return self::text($name, $value, $extras + ['class' => 'email', "attributes" => ["prepend" => '@']]);
  }

  function user($name, $value, $extras = []) {
    return self::text($name, $value, $extras + ['class' => 'user', "attributes" => ["prepend-icon" => 'fa fa-user']]);
  }

  static function daterange($name, $value, $extras = []) {
    return \Fragment::buffer(function() use($name, $value, $extras) { ?>
        <div class='form-inline'>
          <div class='form-group'>
            <?= self::label($name, (isset($extras["date-label"])) ? $extras["date-label"] : "Dates between"); ?>
            <div class='form-group'><?= self::date($name."[From]", $value["From"], $extras) ?></div>
          </div>
          <div class='form-group'>
            <?= self::label($name, "and"); ?>
            <div class='form-group'><?= self::date($name."[To]", $value["To"], $extras) ?></div>
          </div>
        </div>
    <?php
    });
  }

  static function money($name, $value, $extras=[]) {
    if(!isset($extras["attributes"]["prepend"])) $extras["attributes"]["prepend"] = "$";
    if(!$value) $value = null;
    return self::text($name, $value, $extras);
  }

  static function text($name, $value, $extras=[]) {
    $prepend = '';
    $append = '';
    $help = null;
    $type = (isset($extras["type"])) ? $extras["type"] : "text";

    if(isset($extras["attributes"]) && $attributes = $extras["attributes"]) {
      if(isset($attributes["prepend"]) && $attributes["prepend"]) {
        $prepend = $attributes["prepend"];
      } elseif(isset($attributes["prepend-icon"]) && $icon = $attributes["prepend-icon"]) {
        if($icon instanceof \Rex\Icon) {
          $prepend = $icon;
        } else {
          $prepend = '<i class="'.$attributes["prepend-icon"].'"></i>';
        }
      }

      if(isset($attributes["append"]) && $attributes["append"]) {
        $append = $attributes["append"];
      } elseif(isset($attributes["append-icon"]) && $icon = $attributes["append-icon"]) {
        if($icon instanceof \Rex\Icon) {
          $append = $icon;
        } else {
          $append = '<i class="'.$attributes["append-icon"].'"></i>';
        }
      }

      if(isset($attributes["help"]) && $attributes["help"]) {
        $help = (new HelpIcon([
          "class" => "text-help",
          "data-help" => $attributes["help"],
        ]));
      }

      unset($extras["attributes"]);
    }

    if($prepend) $extras["class"] = (isset($extras["class"])) ? $extras["class"] . " form-control" : "form-control";
    if($append) $extras["class"] = (isset($extras["class"])) ? $extras["class"] . " form-control" : "form-control";

    $output = self::input($name, $value, $type, $extras);
    if($help) $output = $output.$help;

    if($prepend || $append) {
      $prepend = ($prepend) ? "<span class='input-group-addon'>".$prepend."</span>" : "";
      $append = ($append) ? "<span class='input-group-addon'>".$append."</span>" : "";

      $output = "<div class='input-group'>{$prepend}{$output}{$append}</div>";
    }

    unset($extras["options"]);

    return $output;
  }

  static function number($name, $value, $extras=[]) {
    return self::input($name, $value, "number", $extras);
  }

  static function password($name, $value, $extras=[]) {
    $output = self::input($name, $value, "password", $extras);
    return $output;
  }

  static function text_options($name, $value, $extras=[]) {
    return self::buffer(function() use($name, $value, $extras) {
      if(isset($extras["help"])) echo self::tag("div", ["class" => "alert alert-info"], $extras["help"]);

      echo self::tag("input", ["type" => "text",
        "name" => $name,
        "data-name" => $name,
        "data-help" => "Please seperate options using a comma.",
        "class" => "text-options",
        "value" => implode(",", $value)
      ]);
    });
  }

  static function radio_set_with_arbitrary_value($name, $value, $extras=[]) {
    return self::tag("fieldset", [], self::tag("ol", [], function() use($name, $value, $extras) {
      $checked = false;
      $output = [];
      foreach($extras["options"] as $radio_value => $radio_label) {
        $options = ["value" => $radio_value];

        if(intval($value) == $radio_value) {
          $checked = true;
          $options["checked"] = "checked";
        }

        $output[] = self::tag("li", [],
          self::tag("label", [],
            self::radio($name, $radio_value, $options)." ".$radio_label
          )
        );
      }

      $options = ["class" => "pull-left", "data-arbitrary" => true];

      if(!$checked && $value) {
        $options["checked"] = "checked";
        $checked = true;
      } else {
        $value = null;
      }

      $output[] = self::tag("li", ["class" => "donation-arbitrary-amount"], [
        self::tag("label", [],
          self::radio($name, $value, $options)
        ),
        self::money($name, $value, [
          "class" => "pull-left arbitrary-amount",
          "placeholder" => @$extras["placeholder"],
        ]),
      ]);

      return $output;
    }));
  }

  static function radioSet($name, $value, $extras=[]) {
    $output = "";
    if (isset($extras["name"]) && $extras["name"]) {
      $name = $extras["name"];
    }

    if (isset($extras["id"])) {
      $id = $extras["id"];
    } else {
      $id = $name;
    }

    $class = "";

    if(isset($extras["class"]) && $extras["class"]) {
      $class = $extras["class"];
    }

    $classes = explode(" ", $class);

    if(@$extras["options"]) foreach($extras["options"] as $key => $label) {

      if($label instanceof Option) {
        $key = $label->value();
        $label = $label->name();

        $checked = $key == $value || in_rex_array($key, $value);

        $output .= "<div class='radio'>";
        $output .= "<label>";
        $extras = ["value" => $key, "checked" => $checked, "id" => $name.$key];

        $output .= Form::radio($name, $key, $extras);

        if(!in_array("hover-star", $classes)) { //This is horrible
          $output .= " $label";
        }
        $output .= "</label>";
        $output .= "</div>";


      } else {
        $checked = $key == $value || in_rex_array($key, $value);
        $output .= "<div class='radio'>";
        $output .= "<label>";
        $extras = ["value" => $key, "checked" => $checked, "id" => $id.$key];
        if(isset($class) && $class) {
          $extras["class"] = $class;
        }
        $output .= Form::radio($name, $key, $extras);

        if(!in_array("hover-star", $classes)) { //This is horrible
          $output .= " $label";
        }
        $output .= "</label>";
        $output .= "</div>";
      }
    }

    return $output;
  }

  static function radio($name, $value, $extras=[]) {
    return self::input($name, $value, "radio", $extras);
  }

  function image($name, $value="", $extras=[]) {
    $preview = '';
    if(!$this) throw new Exception;
    if(isset($extras["ImageLoc"]) && $extras["ImageLoc"]) {
      $image_obj = $extras["ImageLoc"];
    } elseif($this->linked_object) {
      $image_obj = $this->linked_object->$name();
    } else {
      $image_obj = null;
    }

    if(!is_rex_null($image_obj)) {
      $preview = $image_obj->img(["height" => 100]) . "<a class='btn btn-danger remove-upload'><i class='glyphicon glyphicon-remove'></i></a>";
    }
    return self::file($name, $value, $extras). self::hidden('remove'.$name, false) . "<div class='preview-upload clearfix'>$preview</div>";
  }

  static function client_image($name, $value="", $extras=[]) {
    $output = '';

    $circle = (isset($extras["circle"]) && $extras["circle"]) ? "img-circle" : "";
    $existing_image = !is_rex_null($value);

    $change_text = "Change";
    $upload_text = "Upload image";
    $id = self::parseID(isset($extras["id"]) ? $extras["id"] : $name);

    $remove = new Icon\Remove;
    $output .= "<div class='row image-component'>";
      $output .= self::hidden($name, $value);
      if($existing_image) {
        $output .= $value->img(["class" => "preview {$circle}"]);
          $output .= "<div class='col-xs-6'>";
            $output .= "<a class='btn btn-default upload-btn' data-toggle='modal' data-target='#upload_modal' data-target-input='#{$id}' data-alt-text='{$upload_text}'>{$change_text}</a>";
          $output .= "</div>";
          $output .= "<div class='col-xs-6'>";
            $output .= "<a class='btn btn-danger remove-btn' data-target='#{$id}'>{$remove}</a>";
          $output .= "</div>";
      } else {
        $output .= '<img class="preview '.$circle.' hide" src="" />';
          $output .= "<div class='col-xs-6'>";
            $output .= "<a class='btn btn-default upload-btn' data-toggle='modal' data-target='#upload_modal' data-target-input='#{$id}' data-alt-text='{$change_text}'>{$upload_text}</a>";
          $output .= "</div>";
          $output .= "<div class='col-xs-6'>";
            $output .= "<a class='btn btn-danger remove-btn hide' data-target='#{$id}'>{$remove}</a>";
          $output .= "</div>";
      }
    $output .= "</div>";



    return $output;
  }

  static function file($name, $value = "", $extras = []) {
		$output = "";
		if(isset($extras["Download"]) && $extras["Download"] && $value) {
			$output = " <a href='".$value."' target='_blank' class='btn btn-xs'><i class='glyphicon glyphicon-file'></i> View current upload</a>";
		}
    return self::input($name, $value, "file", $extras).$output;
  }


  static function multipleselect($name, $value, $extras=[]) {
    return self::select($name, $value, $extras + ["multiple" => true]);
  }

  static function select($name, $value, $extras=[]) {
    if(!@$extras["options"]) {
      $options = [];
    } else {
      $options = @$extras["options"];
    }
    unset($extras["options"]);

    if(isset($extras["attributes"])) { //backwards compatibility
      $extras = array("name" => $name) + $extras["attributes"];
    }

    if(isset($extras["multiple"]) && $extras["multiple"]) {
      $extras["multiple"] = "multiple";
      $name = $name.'[]';
    }

    $extras["id"] = self::parseID(isset($extras["id"]) ? $extras["id"] : $name);

    return self::tag("select", ["name" => $name] + $extras, self::options($options, $value, @$extras["numkeys"]));
  }

  static $dropdown_cnt = 0;
  static function bs_dropdown($name, $options) {
    $cnt = self::$dropdown_cnt ++; ?>
    <div class="dropdown">
      <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu<?= $cnt ?>" data-toggle="dropdown" aria-expanded="true">
        <?= $name ?>
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu<?= $cnt ?>">
        <?php foreach($options as $option => $link): ?>
          <li role="presentation"><a role="menuitem" tabindex="-1" href="<?= $link ?>"><?= $option ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php
  }

  static function rich_textarea($name, $value, $extras=[]) {
    $extras["class"] = (isset($extras["class"])) ? implode(" ", [$extras["class"], "tiny_mce"]) : "tiny_mce";

    return self::textarea($name, htmlentities($value), $extras);
  }

  static function simple_rich_textarea($name, $value, $extras=[]) {
    $extras["class"] = (isset($extras["class"])) ? implode(" ", [$extras["class"], "simple_tiny_mce"]) : "simple_tiny_mce";
    return self::textarea($name, htmlentities($value), $extras);
  }

  static function textarea($name, $value, $extras = []) {
    $extras = (is_array($extras) ? $extras : []) + array("name" => $name, "rows" => 8, "cols" => 60);
    if(!isset($extras["id"])) { $extras["id"] = $name; }

    $is_tinymce = (in_array("tiny_mce", explode(" ", @$extras["class"])));

    return self::tag("textarea", $extras, $value, true, $is_tinymce);
  }

}

class Fragment extends BaseFragment {
	//04/10/06 JS
	//all fragments must implement their constructor function to
	//render the fragment
	//14/10/06 JS
	//All fragment classes must now be named as $nameFragment, eg. FormFragment
	var $data;
	var $name;
	var $path;
  var $silent;
  var $t_path;

  static protected function fragmentSearch($name, $path, $baseFolder = null, $recursive = true) {
 		$filename = $name;

    $searchPath = ["fragments/$path"];

    if(!is_null($baseFolder)) $searchPath[] = $baseFolder;

    if($name[0] == '/') {
      $searchPath = ["fragments/".dirname($filename)];
      $file = basename($filename);
    } else {
      $file = "$filename";
    }

    $searcher = new PathSearcher($searchPath, $recursive);
    return $searcher->find($file, "php");
  }

  static function create_t_path($path) {
    return strtr($path, ["/" => "."]);
  }

  static function checkFragment($name, $fragmentPath, $baseFolder = null, $recursive = true) {
    return count(static::fragmentSearch($name, $fragmentPath, $baseFolder, $recursive)) != 0;
  }

  static function create($name, $path, $app, $baseFolder = null) {
    \Log::debug("Fragment::create($name, $path, basefolder = $baseFolder");
    $files = static::fragmentSearch($name, $path, $baseFolder);
    if(!count($files)) throw new Exception ("The fragment $name does not exist");

    list($foundFolder, $foundFile) = each($files);
    $file = basename($foundFile, ".php");

    $classFile = $foundFolder."/{$file}_class.php";
    $full_class_name = strtr($foundFolder."/".$file, ["/" => '\\']);
    $full_class_name = strtr($full_class_name, ['\\\\' => '\\']);

    if(file_exists($classFile)) {
      include_once($classFile);
    }

    \Log::debug("Fragment ClassName check: $full_class_name");

    if(class_exists($full_class_name)) {
      $classname = $full_class_name;
    } elseif (class_exists($file."Fragment")){
      $classname = $file."Fragment";
    } else {
      $classname = "Fragment";
    }
    return new $classname($file,$app,$foundFolder, static::create_t_path($path));
  }

	function __construct($name,$App, $path = NULL, $t_path=null){
		parent::__construct($App);

    $file = $path."/$name.php";

		$this->name = basename($name, ".php");
		$this->path = dirname($file);
    $this->fragment_path = substr($this->path, 10);
    $this->t_path = $t_path;
	}

  function renderPartial($fragment, $data = array(), $return = false) {
    $this->renderFragment($fragment, $data, $return);
  }

  function renderFragment($fragment, $data = array(), $return = false) {
    $Fragment = Fragment::create($fragment, $this->fragment_path, $this->App, null);
    return $Fragment->render($data, $return);
  }



  //do silent
	function render($data, $return = false) {
    $this->data = $data;
    $this->beforeRender();

		//grab name minus the fragment ending
    $name = $this->name;

		$path = $this->path;

    $data = $this->data;


		if (file_exists( $path . "/$name.php")){
      $execute = function() use ($path, $name, $data, $return) {
        if (is_array($this->data)){
          extract($this->data,EXTR_PREFIX_ALL,''); //come out with underscores eg $_title
        }

        $__here = $this->here();
        $__action = $this->action();

        if(!App::isLive() && !$return) echo "<!-- rendering: $path/$name.php -->\n\r";
        include( $path. "/$name.php");
      };

      if($return) {
        return BaseFragment::buffer($execute);
      } else {
        $execute();
        return true;
      }
    } else {
      throw new Exception ("Fragment not found $path / $name");
    }
	}

  function is_silent() {
    return false;
  }

	function beforeRender(){
	}

  function sort($col) {
    return SortHelper::link($this->currentURL(), $col);
  }

  function sortLink($col, $text) {
    return "<a href='".$this->sort($col)."' title='Sort on $col'>$text</a>";
  }

  function valid_bootstrap_status_class($class) {
    return in_array($class, ["danger", "success", "warning", "info", "inverse", "default"], true);
  }

  function label_class($status) {
    $class = ($this->valid_bootstrap_status_class($status)) ? $status : $this->default_status_class($status);
    return "label-".$class;
	}

  function default_status_class($condition) {
    return @[
      "incomplete" => "warning",
      "complete" => "success",
      "pending" => "info",
      "outstanding" => "info",
      "checked" => "success"
    ][$condition];
  }

  function registration_complete_status_class($status) {
		if($status == "incomplete") {
			return "danger";
		} elseif($status == "complete") {
			return "success";
		} else {
			return "warning";
		}
  }

  function section_heading($heading, $status=null, $library='glyphicon', $class='success') {
    if($status == "complete") $icon = $this->icon('ok', 'glyphicon', 'success');
    elseif($status == "incomplete") $icon = $this->icon('remove', 'glyphicon','danger');
    elseif($status == "outstanding") $icon = $this->icon('usd', 'glyphicon','danger');
    elseif($status == "open") $icon = $this->icon('unlock', 'fa', 'primary');
    elseif($status == "closed") $icon = $this->icon('lock', 'fa', 'primary');
    elseif($status == "pending") $icon = $this->icon('time', 'glyphicon','primary');
    elseif($status == "new") $icon = $this->icon('pencil-square-o', 'fa','primary');
    elseif($status == "edit") $icon = $this->icon('pencil-square-o', 'fa','primary');
    elseif($status == "expiring") $icon = $this->icon('time', 'glyphicon','warning');
    elseif($status == "expired") $icon = $this->icon('time', 'glyphicon','danger');
    elseif(is_null($status)) $icon = null;
    else $icon = $this->icon($status, $library, $class);

    return "{$icon} $heading";
  }

  static function create_tag_string($fields, $prefix = "") {
    return implode(",", array_map(function($i) use ($prefix) {
      return '{{'.($prefix ? "$prefix." : ""). $i.'}}'; },
    $fields));
  }

  static function cache($key, $function) {
    if($cached = Cache::get($key)) {
      echo $cached;
    } else {
      echo Cache::store($key, self::buffer($function));
    }
  }

  function form_for($object, $options = [], $output) {
    return new Form2($this, $object, $options, $output);
  }

  static function bootstrap_column_class($num_columns) {
    if($num_columns == 1) {
      $sm_size = 12;
      $md_size = 12;
    } elseif($num_columns == 2) {
      $sm_size = 6;
      $md_size = 6;
    } elseif($num_columns == 3) {
      $sm_size = 4;
      $md_size = 4;
    } elseif($num_columns == 4) {
      $sm_size = 6;
      $md_size = 3;
    } elseif($num_columns == 6) {
      $sm_size = 4;
      $md_size = 2;
    }
    $xs_size = 12;

    $column_class = "col-xs-{$xs_size} col-sm-{$sm_size} col-md-{$md_size}";

    return $column_class;
  }

  /**
   * print_bootstrap_column_layout - create a column layout with content for collection of items.
   *
   * $items - a collection of items
   * $print - a closure to generate content for each item
   * $num_columns - the number of columns to print before wrapping
   * $row_classes - Array - classes to add to each row
   * $extra_column_classes - Array - additional classes to add to each column
   **/
  static function print_bootstrap_column_layout($items, $print, $num_columns, $row_classes=[], $extra_column_classes=[]) {
    $column_class = self::bootstrap_column_class($num_columns) . " " . implode(" ", $extra_column_classes);

    $count = 0;
    $row = 0;
    $rows = [];

    foreach($items as $item) {
      $content = (is_callable($print)) ? $print($item, $count) : "";
      $content = "<div class='{$column_class}'>{$content}</div>";

      if($count != 0 && ($count % $num_columns) == 0) $row ++;

      $rows[$row][] = $content;

      $count ++;
    }

    $row_classes = implode(" ", $row_classes);

    return implode("", array_map(function($row) use($num_columns, $row_classes) {
      return "<div data-num-columns='{$num_columns}' class='row {$row_classes}'>".implode(" ", $row)."</div>";
    }, $rows));
  }
  static function flexbox_width_class($num_columns) {
    if(!in_array($num_columns, [1, 2, 3, 4, 6])) {
      $num_columns = 1;
    }
    return "flexbox-width-{$num_columns}";
  }

  static function flexbox_layout($items, $print, $num_columns, $container_class='', $item_class='') {
    $width_class = self::flexbox_width_class($num_columns);
    return self::tag("div", ["class" => "flexbox-container flexbox-default {$width_class} {$container_class}"], function() use($items, $print, $item_class) {
      return self::buffer(function() use($items, $print, $item_class) {
        $count = 0;
        foreach($items as $item) {
          echo self::tag("div", ["class" => "flexbox-item {$item_class}"], function() use($item, $print, $count) {
            return $print($item, $count);
          });

          $count += 1;
        }
      });
    });
  }

  function widget($name, $value, $extra = []) {
    return new \Fragment\Widget($this, $name, $value, $extra);
  }
}
