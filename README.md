# Wire AI Chatbot

A Drupal module that provides a centered, full-screen AI chatbot experience using the Drupal Wire framework. This module enhances the base AI Chatbot functionality with a modern, reactive interface inspired by ChatGPT.

## Features

- **Modern Interface**: ChatGPT-style design with a minimalist input bar that expands to a full conversation view
- **Reactive Components**: Real-time updates and interactions without page reloads
- **Markdown Support**: Automatic conversion of markdown links to clickable HTML links
- **Smooth Animations**: Elegant transitions between collapsed and expanded states
- **Thoughtful UX**: Visual feedback during loading with spinner in the send button
- **Keyboard Support**: Send messages with Enter key, use Shift+Enter for new lines
- **Global or Block Placement**: Use site-wide or on specific pages
- **Fully Configurable**: Customize appearance, behavior, and AI Assistant integration
- **Responsive Design**: Works perfectly on all devices from mobile to desktop
- **Accessibility Features**: Built with accessibility in mind

## Requirements

- Drupal 11
- PHP 8.1 or higher
- [Drupal Wire](https://www.drupal.org/project/wire) module
- [AI Assistant](https://www.drupal.org/project/ai_assistant) module

## Installation

1. Install the module using Composer:
   ```
   composer require drupal/wire_ai_chatbot
   ```

2. Enable the module:
   ```
   drush en wire_ai_chatbot
   ```

3. Configure at least one AI Assistant in the AI Assistant module.

## Configuration

1. Navigate to `/admin/config/user-interface/wire-ai-chatbot` to configure the chatbot settings.

2. Configure the following options:
   - **Enable global chatbot**: When enabled, the chatbot will appear on all pages.
   - **Default AI Assistant**: Select which AI Assistant to use with the chatbot.
   - **Chatbot Title**: The title displayed in the chatbot header.
   - **Button Position**: Choose where the chatbot trigger button appears.
   - **Primary Color**: Set the primary color for the chatbot button and accents.

## Usage

### As a global chatbot

When the "Enable global chatbot" option is enabled in the configuration, the chatbot will appear on all pages of your site. Users can click the chat button to open the chatbot interface.

### As a block

1. Navigate to the Block layout page (`/admin/structure/block`).
2. Place the "Wire AI Chatbot" block in your desired region.
3. Configure block visibility settings as needed.

## Advantages of Using Wire Drupal for AI Chatbot

The Wire AI Chatbot leverages the Drupal Wire framework, which offers several key advantages:

### 1. Reactive Experience Without JavaScript Complexity

- **Reactive Components**: Wire provides a reactive programming model where PHP components can update in real-time without writing complex JavaScript.
- **Server-Rendered HTML**: All content is server-rendered HTML, improving SEO and initial load performance.
- **State Management**: Wire handles state management between the server and client automatically.

### 2. Developer Productivity

- **PHP-Centric Development**: Build dynamic interfaces using familiar PHP/Drupal patterns.
- **Simplified Architecture**: No need for separate frontend and backend codebases.
- **Reduced Context Switching**: Work primarily in PHP with minimal JavaScript requirements.

### 3. User Experience Improvements

- **Reduced Page Loads**: Chat interactions happen without page refreshes, creating a smoother experience.
- **Persistent Context**: Conversation state is maintained without relying on complex client-side state management.
- **Progressive Enhancement**: Works with basic JavaScript and gracefully enhances with more capabilities.

### 4. Performance Benefits

- **Smaller Payload Size**: Sends only HTML diffs instead of full JSON responses.
- **Reduced Client-Side Processing**: Less JavaScript to parse and execute on the client.
- **Server-Side Rendering**: Initial load performance benefits from server rendering.

### 5. Integration with Drupal Ecosystem

- **Seamless AI Integration**: Directly interfaces with Drupal's AI Assistant API.
- **Theme System Compatibility**: Works with Drupal's theme system for consistent styling.
- **Block Placement**: Uses Drupal's familiar block system for placement and visibility rules.

## Customization

### Theme Integration

The chatbot uses CSS variables for styling, which can be overridden in your theme's CSS:

```css
.wire-ai-chatbot {
  --chatbot-primary: #your-primary-color;
  --chatbot-text: #your-text-color;
  --chatbot-bg: #your-background-color;
  --chatbot-border: #your-border-color;
  --chatbot-user-bg: #your-user-message-bg;
  --chatbot-assistant-bg: #your-assistant-message-bg;
  --chatbot-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  --chatbot-transition: all 0.3s ease;
}
```

### Template Customization

You can override the template by copying the `templates/wire/wire_ai_chatbot.html.twig` file to your theme's templates directory and modifying it as needed.

## Implementation Details

The Wire AI Chatbot builds upon the base AI Chatbot module to create a more modern, reactive interface:

- **Wire Component**: The `WireAiChatbot` component manages state and AI interaction.
- **Markdown Rendering**: Uses Marked.js for markdown-to-HTML conversion.
- **Loading States**: Visual feedback during API requests with spinner indicators.
- **Responsive Layout**: Adapts to different screen sizes with appropriate layouts.
- **Progressive Enhancement**: Works with JavaScript disabled but enhances with JS.

## Troubleshooting

- If the chatbot doesn't appear, ensure that you have properly configured an AI Assistant in the AI Assistant module.
- Check that the module's JavaScript and CSS assets are being loaded correctly.
- Verify that there are no JavaScript errors in the browser console.
- For Wire-specific issues, check that the Wire Drupal module is correctly installed and working.

## Credits

Developed for Property Pilot by the development team.
