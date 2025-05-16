# Development Brief: ChatGPT-Style AI Chatbot Using Drupal Wire

## Introduction
After checking the initial code structure and reviewing the Drupal Wire documentation, this brief outlines the implementation of a ChatGPT-style AI chatbot experience using the Drupal Wire framework.

## Project Overview
We'll create a modern ChatGPT-style interface with an input bar fixed at the bottom center of the screen that expands into a full conversation view in the middle of the screen when in use. This approach avoids the timeout issues encountered with DeepChat while providing a familiar and engaging user experience.

## Technical Assessment
The generated Wire component provides a minimal starting structure:

```php
#[WireComponent(id: 'wire_ai_chatbot')]
class WireAiChatbot extends WireComponentBase {
  public function render(): ?View {
    return View::fromTpl('wire_ai_chatbot');
  }
}
```

This gives us a clean foundation to implement our ChatGPT-style interface while following Drupal Wire best practices.

## Implementation Plan

### 1. Core Wire Component
Enhance the WireAiChatbot class with:
- State management for expanded/collapsed views
- Message history tracking
- Integration with the AI Assistant API
- Smooth transition animations

### 2. UI/UX Design
Create a ChatGPT-style interface with two key states:

**Collapsed State:**
- Minimalist input bar fixed to the bottom center of the screen
- Subtle prompt text and send button
- Clear visual indicator that it's an AI assistant

**Expanded State:**
- Full conversation view that animates into the center of the screen
- Message history displayed above the input area
- Maintains the bottom input bar position
- Animated typing indicators when AI is responding

### 3. Integration Points
- **AI Assistant API**: Use Drupal's dependency injection to access the AI Assistant
- **TailwindCSS**: Use utility classes for responsive design and animations
- **AlpineJS**: Handle state transitions and micro-interactions
- **Motion.js**: Implement smooth animations for expanding/collapsing

### 4. Technical Requirements
- Wire Drupal framework for reactive components
- Based on existing AI chatbot (web/modules/contrib/ai/modules/ai_chatbot)
- Mobile-responsive design that works on all screen sizes
- Keyboard accessibility and screen reader support

## Component States

1. **Initial State**: Only the chat input bar is visible at the bottom center of the screen
2. **Focus State**: Input bar subtly highlights when focused
3. **Active State**: Full conversation view expands upward into the center of the screen
4. **Response State**: Shows typing indicators and streams in the AI response
5. **Persistent State**: Maintains conversation context between interactions

## Code Structure

The implementation requires:

1. **Wire Component** (`WireAiChatbot.php`): Core component with state management
2. **Inline Template**: Using `View::fromString()` with TailwindCSS for styling
3. **Module File** (`wire_ai_chatbot.module`): For any required hooks
4. **Optional CSS**: For animations not easily accomplished with Tailwind
5. **Libraries Definition**: To include any additional assets

## Implementation Notes

1. Use `wire:click` for toggling between collapsed/expanded states
2. Apply `x-transition` directives for smooth expand/collapse animations
3. Use Motion.js for advanced typing indicator animations
4. Ensure the interface remains unobtrusive when collapsed
5. When expanded, maintain focus on the conversation flow
6. Design should resemble the familiar ChatGPT interface
7. Ensure all text remains readable against any background

## Performance Considerations

1. Lazy-initialize the full conversation view
2. Stream AI responses instead of waiting for complete answers
3. Optimize animations for smooth performance on mobile devices
4. Use efficient DOM-diffing through Wire's reactive updates
5. Consider debouncing user input for better performance

This implementation provides a familiar ChatGPT-style experience while leveraging Drupal Wire's reactive capabilities, offering users an intuitive and engaging way to interact with the AI chatbot.