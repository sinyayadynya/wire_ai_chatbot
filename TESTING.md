# Testing the Wire AI Chatbot

This document provides instructions for testing the Wire AI Chatbot module.

## Prerequisites

Before testing, make sure you have:

1. Drupal 11 installed with the following modules enabled:
   - Drupal Wire module
   - AI Assistant API module
   - AI Chatbot module
   - Wire AI Chatbot module

2. At least one AI Assistant configured in the AI Assistant API module.

## Testing the Global Chatbot

1. Navigate to `/admin/config/user-interface/wire-ai-chatbot`.
2. Enable the global chatbot option.
3. Select an AI Assistant from the dropdown.
4. Configure the appearance settings as desired.
5. Save the configuration.
6. Navigate to any page on the site.
7. Verify that the chatbot button appears in the position you specified.
8. Click the chatbot button to expand it.
9. Verify that the UI displays correctly with the header showing the bot name.
10. Type a message and press Enter or click the send button.
11. Verify that the message appears in the chat and that the AI Assistant responds.
12. Verify that the chatbot can be collapsed by clicking the close button.

## Testing the Block Placement

1. Navigate to `/admin/structure/block`.
2. Click "Place block" in your desired region.
3. Find the "Wire AI Chatbot" block and click "Place block".
4. Configure the block settings, selecting an AI Assistant and customizing appearance.
5. Save the block configuration.
6. Navigate to a page where the block should appear.
7. Verify that the chatbot appears and functions correctly.
8. Test that the block-specific settings (such as AI Assistant, bot name, etc.) are applied correctly.

## Testing Message Interaction

1. Open the chatbot (either global or block-placed).
2. Type a question or statement and send it.
3. Verify that the message appears in the chat with your user name/avatar.
4. Verify that the AI Assistant responds appropriately.
5. Check that the chat history is maintained if you close and reopen the chatbot.
6. Verify that the loading indicator appears while the AI is processing.

## Testing Responsiveness

1. Test the chatbot on different devices (desktop, tablet, mobile).
2. Verify that the UI adapts correctly to different screen sizes.
3. Check that the typing experience is smooth on mobile devices.
4. Verify that the expanded chatbot doesn't overflow the screen on small devices.

## Troubleshooting

If any issues are encountered during testing:

1. Check the Drupal logs for error messages: `/admin/reports/dblog`.
2. Verify that all required dependencies are installed and properly configured.
3. Ensure that the AI Assistant you selected is properly configured and functional.
4. Check browser developer tools for any JavaScript errors.

## Reporting Issues

If you find any bugs or have feature requests, please report them in the project issue queue.