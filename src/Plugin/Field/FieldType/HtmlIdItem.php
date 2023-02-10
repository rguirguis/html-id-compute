<?php

namespace Drupal\html_id_computed\Plugin\Field\FieldType;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;


/**
 * Variant of the 'string' field that generate HTML ID
 *
 * @FieldType(
 *   id = "html_id_computed",
 *   label = @Translation("HTML ID"),
 *   description = @Translation("A computed HTML ID for entities."),
 *   default_widget = "string_textfield",
 *   default_formatter = "string"
 * )
 */
class HtmlIdItem extends StringItem {

  /**
   * Whether or not the value is calculated.
   *
   * @var bool
   */
  protected $isCalculated = False;

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    $this->ensureCalculated();
    return parent::__get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $this->ensureCalculated();
    return parent::isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $this->ensureCalculated();
    return parent::getValue();
  }

  /**
   * Calculates the value of the field and sets it.
   */
  public function ensureCalculated() {
    if ($this->isCalculated) {
      return;
    }
    $entity = $this->getEntity();
    if (!$entity->isNew()) {
      $html_id = $this->getHtmlId($this->getEntity());
      if (!empty($html_id)) {
        $this->setValue($html_id);
      }
    }
    $this->isCalculated = TRUE;
  }

  protected function getHtmlId(EntityInterface $entity) {
    if ($entity instanceof ParagraphInterface) {
      return $this->getParagraphHtmlId($entity);
    }
    if ($entity instanceof NodeInterface) {
      return $this->getNodeHtmlId($entity);
    }
  }

  protected function getParagraphHtmlId(EntityInterface $paragraph) {
    if (! $paragraph instanceof ParagraphInterface) {
      return;
    }
    $available_fields = $paragraph->getFields(false);
    $potentialTitles = \preg_grep('/title|head/', \array_keys($available_fields));
    foreach ($potentialTitles as $fieldName) {
      $fieldItemList = $available_fields[$fieldName];
      /* $fieldItemList Drupal\Core\Field\FieldItemListInterface */
      if ( !$fieldItemList->isEmpty() && 'field_item:string' == $fieldItemList->first()->getDataDefinition()->getDataType()) {
        return Html::getUniqueId($fieldItemList->first()->getString());
      }
    }
    return Html::getUniqueId('paragraph-' . $paragraph->id());
  }

  protected function getNodeHtmlId(EntityInterface $node) {
    if (! $node instanceof NodeInterface) {
      return;
    }
    $html_id = Html::getUniqueId($node->getTitle());
    if (3 > strlen($html_id)) {
      $html_id = Html::getUniqueId('content-' . $node->id());
    }
    return $html_id;
  }

}
