<?php declare(strict_types = 1);

namespace Drupal\wire_ai_chatbot\Plugin\WireComponent;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ai_assistant_api\AiAssistantApiRunner;
use Drupal\ai_assistant_api\Data\UserMessage;
use Drupal\wire\View;
use Drupal\wire\WireComponentBase;
use Drupal\wire\Annotation\WireComponent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AI Chatbot Wire component.
 *
 * @WireComponent(
 *   id = "wire_ai_chatbot"
 * )
 */
class WireAiChatbot extends WireComponentBase {

  /**
   * Whether the chatbot is expanded.
   *
   * @var bool
   */
  public bool $expanded = false;

  /**
   * The message input value.
   *
   * @var string
   */
  public string $message = '';

  /**
   * Whether the assistant is responding.
   *
   * @var bool
   */
  public bool $isLoading = false;

  /**
   * The chat messages.
   *
   * @var array
   */
  public array $messages = [];

  /**
   * The assistant entity ID.
   *
   * @var string|null
   */
  public ?string $assistantId = null;

  /**
   * The bot name.
   *
   * @var string
   */
  public string $botName = 'AI Assistant';

  /**
   * The bot image.
   *
   * @var string
   */
  public string $botImage = '/modules/custom/wire_ai_chatbot/images/chatbot_icon.svg';

  /**
   * The user name.
   *
   * @var string
   */
  public string $userName = 'You';

  /**
   * The user image.
   *
   * @var string
   */
  public string $userImage = '/modules/custom/wire_ai_chatbot/images/user_icon.svg';
  
  /**
   * The placeholder text for the input field.
   *
   * @var string
   */
  public string $inputPlaceholder = 'Ask me anything...';

  /**
   * The thread ID for message history.
   *
   * @var string
   */
  public string $threadId = '';

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The AI Assistant API runner.
   */
  protected AiAssistantApiRunner $aiAssistantRunner;

  /**
   * The current user.
   */
  protected AccountProxyInterface $currentUser;

  /**
   * The config factory.
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructs a new WireAiChatbot object.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    AiAssistantApiRunner $ai_assistant_runner,
    AccountProxyInterface $current_user,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->aiAssistantRunner = $ai_assistant_runner;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
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
      $container->get('ai_assistant_api.runner'),
      $container->get('current_user'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function mount(): void {
    // Get settings from configuration or context.
    $config = $this->configFactory->get('wire_ai_chatbot.settings');
    
    // Context values have priority over config.
    if (isset($this->attributes['assistantId']) && !empty($this->attributes['assistantId'])) {
      $this->assistantId = $this->attributes['assistantId'];
    }
    else {
      $this->assistantId = $config->get('default_assistant');
    }
    
    // Set chatbot display properties from context or config.
    if (isset($this->attributes['botName']) && !empty($this->attributes['botName'])) {
      $this->botName = $this->attributes['botName'];
    }
    else {
      $this->botName = $config->get('bot_name') ?: 'AI Assistant';
    }
    
    if (isset($this->attributes['botImage']) && !empty($this->attributes['botImage'])) {
      $this->botImage = $this->attributes['botImage'];
    }
    else {
      $this->botImage = $config->get('bot_image') ?: '/modules/custom/wire_ai_chatbot/images/chatbot_icon.svg';
    }
    
    if (isset($this->attributes['inputPlaceholder']) && !empty($this->attributes['inputPlaceholder'])) {
      $this->inputPlaceholder = $this->attributes['inputPlaceholder'];
    }
    else {
      $this->inputPlaceholder = $config->get('input_placeholder') ?: 'Ask me anything...';
    }
    
    // User info.
    $this->userName = $this->currentUser->isAuthenticated() ? $this->currentUser->getDisplayName() : 'You';
    
    // Set user image (you could enhance this to get the user picture).
    $this->userImage = '/modules/custom/wire_ai_chatbot/images/user_icon.svg';
    
    // If an assistant ID is set, load the first message.
    if ($this->assistantId) {
      $this->loadAssistant();
    }
    
    // Register a browser event listener - in Wire Drupal we handle this differently.
    // We'll set up listeners in the template instead.
  }

  /**
   * Loads the AI assistant and initial message.
   */
  protected function loadAssistant(): void {
    try {
      // Load the assistant entity.
      $assistant = $this->entityTypeManager->getStorage('ai_assistant')->load($this->assistantId);
      
      if ($assistant) {
        // Set the assistant in the runner.
        $this->aiAssistantRunner->setAssistant($assistant);
        
        // Get thread ID for history.
        $this->threadId = $this->aiAssistantRunner->getThreadsKey();
        
        // Check if there's any message history.
        $history = $this->aiAssistantRunner->getMessageHistory();
        
        if (!empty($history)) {
          // Convert history to our message format.
          foreach ($history as $historyMessage) {
            $this->messages[] = [
              'role' => $historyMessage['role'],
              'content' => $historyMessage['message'],
              'timestamp' => date('H:i', $historyMessage['timestamp']),
            ];
          }
        }
        else {
          // Add welcome message if no history exists.
          // First try to get from module settings, then fallback to assistant entity, then default
          $siteConfig = \Drupal::config('wire_ai_chatbot.settings');
          $welcomeMessage = $siteConfig->get('welcome_message') ?: 
            ($assistant->get('welcome_message') ?: 'Hello! How can I help you today?');
          $this->messages[] = [
            'role' => 'assistant',
            'content' => $welcomeMessage,
            'timestamp' => date('H:i'),
          ];
        }
      }
    }
    catch (\Exception $e) {
      // Log errors but don't expose them to the user.
      \Drupal::logger('wire_ai_chatbot')->error('Error loading assistant: @message', ['@message' => $e->getMessage()]);
    }
  }

  /**
   * Toggle the expanded state of the chatbot.
   */
  public function toggleExpanded(): void {
    $this->expanded = !$this->expanded;
  }

  /**
   * Send a message to the AI assistant.
   */
  public function sendMessage(): void {
    // Skip empty messages.
    if (empty($this->message)) {
      return;
    }

    // Add user message to the conversation.
    $this->messages[] = [
      'role' => 'user',
      'content' => nl2br(htmlspecialchars($this->message)),
      'timestamp' => date('H:i'),
    ];
    
    // Save the message before clearing the input field.
    $messageText = $this->message;
    $this->message = '';
    
    // Set loading state and expand the chatbot.
    $this->isLoading = true;
    $this->expanded = true;
    
    try {
      // Make sure we have a valid assistant.
      if (!$this->assistantId) {
        throw new \Exception('No AI Assistant configured.');
      }
      
      // Load the assistant entity.
      $assistant = $this->entityTypeManager->getStorage('ai_assistant')->load($this->assistantId);
      if (!$assistant) {
        throw new \Exception('AI Assistant not found.');
      }
      
      // Set the assistant in the runner.
      $this->aiAssistantRunner->setAssistant($assistant);
      
      // Create a user message object.
      $userMessageObj = new UserMessage($messageText);
      $this->aiAssistantRunner->setUserMessage($userMessageObj);
      
      // Process the message.
      $response = $this->aiAssistantRunner->process();
      
      // Extract the response message based on the API structure
      $assistantResponseText = '';
      
      // Get the normalized response
      $normalizedResponse = $response->getNormalized();
      
      // Check if it's a ChatMessage
      if (is_object($normalizedResponse) && method_exists($normalizedResponse, 'getText')) {
        $assistantResponseText = $normalizedResponse->getText();
      }
      // Check if it's an array
      elseif (is_array($normalizedResponse) && !empty($normalizedResponse)) {
        // Take the first element if it's an array of messages
        $firstElement = reset($normalizedResponse);
        if (is_object($firstElement) && method_exists($firstElement, 'getText')) {
          $assistantResponseText = $firstElement->getText();
        }
        elseif (is_string($firstElement)) {
          $assistantResponseText = $firstElement;
        }
      }
      // If we still can't get a text, convert to string as fallback
      if (empty($assistantResponseText)) {
        $assistantResponseText = (string) $normalizedResponse;
      }
      
      $assistantMessage = [
        'role' => 'assistant',
        'content' => $assistantResponseText,
        'timestamp' => date('H:i'),
      ];
      $this->messages[] = $assistantMessage;
    }
    catch (\Exception $e) {
      // Add error message to the chat.
      $this->messages[] = [
        'role' => 'assistant',
        'content' => 'Sorry, I encountered an error. Please try again later.',
        'timestamp' => date('H:i'),
      ];
      
      // Log the error.
      \Drupal::logger('wire_ai_chatbot')->error('Error processing message: @message', ['@message' => $e->getMessage()]);
    }
    finally {
      // Clear loading state.
      $this->isLoading = false;
    }
  }

  /**
   * Handle shift+enter keypress to allow line breaks.
   *
   * @param array $event
   *   The event object.
   */
  public function handleShiftEnter(array $event = []): void {
    // This is a no-op method. When shift+enter is pressed,
    // we want the default behavior (inserting a new line).
  }
  
  /**
   * Handle the event.stopPropagation method call.
   *
   * This is needed for wire:keydown.shift.enter="$event.stopPropagation()"
   * Wire tries to call this method on the component with the event as argument.
   *
   * @param array $event
   *   The event object.
   */
  public function stopPropagation(array $event = []): void {
    // This is a no-op method, it just needs to exist.
    // The actual event stopping is handled by the Wire framework.
  }

  /**
   * {@inheritdoc}
   */
  public function render(): ?View {
    return View::fromTpl('wire_ai_chatbot');
  }

}
