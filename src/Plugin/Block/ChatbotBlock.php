<?php declare(strict_types = 1);

namespace Drupal\wire_ai_chatbot\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Wire AI Chatbot block.
 *
 * @Block(
 *   id = "wire_ai_chatbot_block",
 *   admin_label = @Translation("Wire AI Chatbot"),
 *   category = @Translation("AI")
 * )
 */
class ChatbotBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The extension path resolver.
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  /**
   * Constructs a new ChatbotBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    array $configuration, 
    $plugin_id, 
    $plugin_definition, 
    EntityTypeManagerInterface $entity_type_manager,
    ExtensionPathResolver $extension_path_resolver
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->extensionPathResolver = $extension_path_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('extension.path.resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'assistant_id' => '',
      'bot_name' => 'AI Assistant',
      'bot_image' => '/core/misc/druplicon.png',
      'primary_color' => '#0073e6',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    // Get a list of available AI assistants.
    $assistants = [];
    try {
      $entities = $this->entityTypeManager->getStorage('ai_assistant')->loadMultiple();
      foreach ($entities as $id => $assistant) {
        $assistants[$id] = $assistant->label();
      }
    }
    catch (\Exception $e) {
      $assistants = [];
    }
    
    if (empty($assistants)) {
      $form['no_assistants'] = [
        '#markup' => $this->t('No AI assistants found. Please <a href=":url">create an AI assistant</a> first.', [
          ':url' => '/admin/config/ai/assistants/add',
        ]),
      ];
    }
    else {
      $form['assistant_id'] = [
        '#type' => 'select',
        '#title' => $this->t('AI Assistant'),
        '#description' => $this->t('Select which AI Assistant to use with this chatbot.'),
        '#options' => $assistants,
        '#default_value' => $config['assistant_id'],
        '#required' => TRUE,
      ];
      
      $form['bot_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Chatbot Title'),
        '#description' => $this->t('The title displayed in the chatbot header.'),
        '#default_value' => $config['bot_name'],
      ];
      
      $form['bot_image'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Bot Image'),
        '#description' => $this->t('Path to the bot avatar image.'),
        '#default_value' => $config['bot_image'],
      ];
      
      $form['primary_color'] = [
        '#type' => 'color',
        '#title' => $this->t('Primary Color'),
        '#description' => $this->t('Set the primary color for the chatbot accents.'),
        '#default_value' => $config['primary_color'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['assistant_id'] = $form_state->getValue('assistant_id');
    $this->configuration['bot_name'] = $form_state->getValue('bot_name');
    $this->configuration['bot_image'] = $form_state->getValue('bot_image');
    $this->configuration['primary_color'] = $form_state->getValue('primary_color');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    
    // Add block-specific settings for the chatbot.
    $build['#attached']['drupalSettings']['wireAiChatbot'] = [
      'assistantId' => $this->configuration['assistant_id'],
      'botName' => $this->configuration['bot_name'],
      'botImage' => $this->configuration['bot_image'],
      'primaryColor' => $this->configuration['primary_color'],
    ];
    
    // Include the chatbot library.
    $build['#attached']['library'][] = 'wire_ai_chatbot/chatbot';
    
    // Render the Wire component using theme system instead of direct template rendering
    $build['content'] = [
      '#theme' => 'wire_ai_chatbot',
      '#wire_ai_chatbot' => [
        'assistantId' => $this->configuration['assistant_id'],
        'botName' => $this->configuration['bot_name'], 
        'botImage' => $this->configuration['bot_image'],
      ],
    ];

    return $build;
  }

}