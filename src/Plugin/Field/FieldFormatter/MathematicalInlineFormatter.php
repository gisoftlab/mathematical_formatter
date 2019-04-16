<?php
/**
 * @file
 * Contains Drupal\mathematical_formatter\Plugin\Field\FieldFormatter\MathematicalFormatter.
 */
namespace Drupal\mathematical_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mathematical_formatter\Services\MathematicalInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'mathematical_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "mathematical_inline_formatter",
 *   label = @Translation("Mathematical Inline Formatter"),
 *   weight = "11",
 *   field_types = {
 *     "string",
 *     "text",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class MathematicalInlineFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

    /**
     * The mathematical parser
     *
     * @var MathematicalInterface
     */
    protected $parser;

    /**
     * MathematicalInlineFormatter constructor.
     * @param $plugin_id
     * @param $plugin_definition
     * @param FieldDefinitionInterface $field_definition
     * @param array $settings
     * @param $label
     * @param $view_mode
     * @param array $third_party_settings
     * @param MathematicalInterface $parser
     */
    public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, MathematicalInterface $parser) {
        parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode) {

        /**
         * @var $math MathematicalService
         */
        $math = \Drupal::service('mathematical_service.compute');

        $elements = [];
        // The ProcessedText element already handles cache context & tag bubbling.
        // @see \Drupal\filter\Element\ProcessedText::preRenderText()
        /**
         * @var $items[] FieldItemList
         */
        foreach ($items as $delta => $item) {
            $elements[$delta] = [
                '#type' => 'processed_text',
                '#text' => $math->parse([$item->value]),
                '#format' => $item->format,
                '#langcode' => $item
                    ->getLangcode(),
            ];
        }
        return $elements;
    }

    /**
     * @param ContainerInterface $container
     * @param array $configuration
     * @param string $plugin_id
     * @param mixed $plugin_definition
     * @return ContainerFactoryPluginInterface|MathematicalFormatter
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $plugin_id,
            $plugin_definition,
            $configuration['field_definition'],
            $configuration['settings'],
            $configuration['label'],
            $configuration['view_mode'],
            $configuration['third_party_settings'],
            // Add any services you want to inject here
            $container->get('mathematical_service.compute')
        );
    }
}
