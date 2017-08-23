<?php

namespace Qordoba;

class TranslateString implements \JsonSerializable {

  private $key;
  private $value;

  public function __construct($key, $value, $section) {
    $this->key      = $key;
    $this->value    = $value;
    $this->section  = $section;
  }

  public function unlink() {
    $this->section->removeTranslationString($this->key);
  }

  public function jsonSerialize() {
    return  $this->value;
  }
}