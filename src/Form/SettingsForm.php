<?php declare(strict_types = 1);

namespace Drupal\wire_ai_chatbot\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Wire AI Chatbot settings.
 */
class SettingsForm extends FormBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wire_ai_chatbot_settings';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable.
   */
  protected function getEditableConfigNames() {
    return ['wire_ai_chatbot.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
      $this->messenger()->addWarning($this->t('No AI assistants found. Please <a href=":url">create an AI assistant</a> first.', [
        ':url' => '/admin/config/ai/assistants/add',
      ]));
    }

    $config = $this->configFactory->get('wire_ai_chatbot.settings');

    $form['global_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Global Chatbot Settings'),
      '#open' => TRUE,
    ];

    $form['global_settings']['enable_global_chatbot'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable global chatbot'),
      '#description' => $this->t('When enabled, the chatbot will appear on all pages.'),
      '#default_value' => $config->get('enable_global_chatbot'),
    ];

    $form['global_settings']['default_assistant'] = [
      '#type' => 'select',
      '#title' => $this->t('Default AI Assistant'),
      '#description' => $this->t('Select which AI Assistant to use with the chatbot.'),
      '#options' => $assistants,
      '#default_value' => $config->get('default_assistant'),
      '#required' => TRUE,
      '#empty_option' => $this->t('- Select an AI Assistant -'),
      '#states' => [
        'required' => [
          ':input[name="enable_global_chatbot"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['appearance'] = [
      '#type' => 'details',
      '#title' => $this->t('Appearance Settings'),
      '#open' => TRUE,
    ];

    $form['appearance']['bot_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chatbot Title'),
      '#description' => $this->t('The title displayed in the chatbot header.'),
      '#default_value' => $config->get('bot_name') ?: 'AI Assistant',
    ];

    $form['appearance']['bot_image'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bot Image'),
      '#description' => $this->t('Path to the bot avatar image.'),
      '#default_value' => $config->get('bot_image') ?: '/core/misc/druplicon.png',
    ];

    $form['appearance']['button_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Button Position'),
      '#description' => $this->t('Choose where the chatbot trigger button appears.'),
      '#options' => [
        'bottom-right' => $this->t('Bottom Right'),
        'bottom-left' => $this->t('Bottom Left'),
      ],
      '#default_value' => $config->get('button_position') ?: 'bottom-right',
      '#states' => [
        'visible' => [
          ':input[name="enable_global_chatbot"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['appearance']['primary_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Primary Color'),
      '#description' => $this->t('Set the primary color for the chatbot button and accents.'),
      '#default_value' => $config->get('primary_color') ?: '#0073e6',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate global chatbot requires an assistant.
    if ($form_state->getValue('enable_global_chatbot') && empty($form_state->getValue('default_assistant'))) {
      $form_state->setErrorByName('default_assistant', $this->t('You must select an AI Assistant when the global chatbot is enabled.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $editable_config = $this->configFactory->getEditable('wire_ai_chatbot.settings');
    $editable_config
      ->set('enable_global_chatbot', $form_state->getValue('enable_global_chatbot'))
      ->set('default_assistant', $form_state->getValue('default_assistant'))
      ->set('bot_name', $form_state->getValue('bot_name'))
      ->set('bot_image', $form_state->getValue('bot_image'))
      ->set('button_position', $form_state->getValue('button_position'))
      ->set('primary_color', $form_state->getValue('primary_color'))
      ->save();

    $this->messenger()->addStatus($this->t('The configuration options have been saved.'));
  }

}