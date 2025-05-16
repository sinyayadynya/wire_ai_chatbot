/**
 * @file
 * JavaScript behaviors for the Wire AI Chatbot.
 */

(function (Drupal, drupalSettings, once) {
  'use strict';

  /**
   * Wire AI Chatbot behavior.
   */
  Drupal.behaviors.wireAiChatbot = {
    chatbotComponents: [],

    attach: function (context, settings) {
      once('wire-ai-chatbot', 'body', context).forEach(function () {
        // Apply theme color from settings
        if (drupalSettings.wireAiChatbot && drupalSettings.wireAiChatbot.primaryColor) {
          // Update CSS variable for the primary color
          document.documentElement.style.setProperty('--chatbot-primary', drupalSettings.wireAiChatbot.primaryColor);
        }
        
        // Only add the global button if enabled in settings
        if (drupalSettings.wireAiChatbot && drupalSettings.wireAiChatbot.buttonPosition) {
          this.addGlobalButton(drupalSettings.wireAiChatbot);
        }

        // Find all Wire AI Chatbot instances
        this.chatbotComponents = document.querySelectorAll('.wire-ai-chatbot');

        // Add auto-resize for textarea
        this.initTextareaAutosize();

        // Scroll to bottom when new messages arrive
        this.initMessageScrolling();
        
        // Convert markdown in chat messages
        this.processMarkdown();
        
        // Set up an observer to process new messages
        this.initMarkdownObserver();
        
        // Wire-specific listeners for message updates
        this.initWireEventListeners();
      }.bind(this));
    },
    
    /**
     * Initialize Wire-specific event listeners
     */
    initWireEventListeners: function() {
      // Process markdown content after Wire updates the DOM
      document.addEventListener('wire:update', function(event) {
        console.log('Wire update detected', event);
        setTimeout(function() {
          Drupal.behaviors.wireAiChatbot.processMarkdown();
        }, 100);
      });
    },
    
    /**
     * Process markdown in chat bubbles
     */
    processMarkdown: function() {
      // Debug if marked is loaded
      if (typeof marked === 'undefined') {
        console.error('Marked.js library is not loaded!');
        return;
      }
      
      console.log('Processing markdown with Marked.js');
      
      // Configure marked with options
      marked.setOptions({
        gfm: true,        // GitHub flavored markdown
        breaks: true,     // Convert line breaks to <br>
        headerIds: false, // Don't add ids to headers
        mangle: false,    // Don't escape html in output
        pedantic: false,  // Be forgiving with markdown syntax
        silent: false     // Show warnings in console for debugging
      });
      
      // Custom renderer for links (now opening in same tab)
      const renderer = new marked.Renderer();
      renderer.link = function(href, title, text) {
        console.log('Rendering link:', href, title, text);
        return '<a class="chatbot-link" href="' + href + '">' + text + '</a>';
      };
      marked.setOptions({ renderer });

      // Directly process links with a regex as an additional fallback
      function processLinksWithRegex(text) {
        // Pattern for markdown links: [text](url)
        const linkPattern = /\[(.*?)\]\((.*?)\)/g;
        return text.replace(linkPattern, function(match, text, url) {
          console.log('Regex found link:', text, url);
          return '<a class="chatbot-link" href="' + url + '">' + text + '</a>';
        });
      }

      // Find all message bubbles containing content
      const bubbles = document.querySelectorAll('.wire-ai-chatbot-bubble');
      console.log('Found bubbles:', bubbles.length);
      
      bubbles.forEach(function(bubble) {
        const text = bubble.textContent.trim();
        if (text) {
          console.log('Processing text:', text);
          try {
            // First try with marked
            const parsedHtml = marked.parse(text);
            console.log('Parsed HTML:', parsedHtml);
            
            // If marked didn't convert the links, use regex fallback
            if (parsedHtml.indexOf('<a') === -1 && text.indexOf('[') > -1) {
              console.log('Using regex fallback');
              bubble.innerHTML = processLinksWithRegex(text);
            } else {
              bubble.innerHTML = parsedHtml;
            }
          } catch (e) {
            console.error('Error parsing markdown:', e);
            // Fallback to regex-based link conversion
            bubble.innerHTML = processLinksWithRegex(text);
          }
        }
      });
    },
    
    /**
     * Set up observer to process new message bubbles
     */
    initMarkdownObserver: function() {
      const self = this;
      const observer = new MutationObserver(function(mutations) {
        let shouldProcess = false;
        
        mutations.forEach(function(mutation) {
          if (mutation.type === 'childList' && mutation.addedNodes.length) {
            // Check if the added nodes contain message bubbles
            for (let i = 0; i < mutation.addedNodes.length; i++) {
              const node = mutation.addedNodes[i];
              if (node.nodeType === 1 && (
                node.classList?.contains('wire-ai-chatbot-bubble') || 
                node.querySelector?.('.wire-ai-chatbot-bubble')
              )) {
                shouldProcess = true;
                break;
              }
            }
          }
        });
        
        if (shouldProcess) {
          // Small delay to ensure content is fully loaded
          setTimeout(function() {
            self.processMarkdown();
          }, 50);
        }
      });
      
      // Observe the message container for changes
      document.querySelectorAll('.wire-ai-chatbot-messages').forEach(function(container) {
        observer.observe(container, { childList: true, subtree: true });
      });
    },

    /**
     * Add the global button to access the chatbot.
     */
    addGlobalButton: function (settings) {
      const button = document.createElement('div');
      button.className = 'wire-ai-chatbot-global-button ' + settings.buttonPosition;
      
      // Set the button's custom color if provided
      if (settings.primaryColor) {
        button.style.backgroundColor = settings.primaryColor;
      }

      // Add chat icon
      button.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
        '<path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H6L4 18V4H20V16Z" fill="white"/>' +
        '</svg>';

      // Add click event to toggle chatbot
      button.addEventListener('click', function () {
        // Dispatch an event that the Wire component can listen for
        document.dispatchEvent(new CustomEvent('wireAiChatbotToggle'));
      });

      document.body.appendChild(button);
    },

    /**
     * Toggle chatbot via JavaScript (used for the global toggle event)
     */
    toggleChatbot: function() {
      // Find any visible chatbot instance
      this.chatbotComponents.forEach(function(component) {
        // Find the toggleExpanded button and click it through Wire
        const toggleButton = component.querySelector('.wire-ai-chatbot-toggle, .wire-ai-chatbot-expand-overlay');
        if (toggleButton) {
          toggleButton.click();
        }
        else {
          // If no toggle button found (might be in collapsed state)
          // Just toggle the expanded/collapsed class
          if (component.classList.contains('collapsed')) {
            component.classList.remove('collapsed');
            component.classList.add('expanded');
          } else {
            component.classList.remove('expanded');
            component.classList.add('collapsed');
          }
          
          // Also trigger a reflow using Drupal's Ajax system to notify Wire
          // that the component state has changed
          if (Drupal.ajax && typeof Drupal.ajax.instances !== 'undefined') {
            // Find any wire Ajax object associated with this component
            const wireInstanceId = component.closest('[wire\\:id]')?.getAttribute('wire:id');
            if (wireInstanceId) {
              // Try to find a matching Ajax instance and trigger it
              Object.values(Drupal.ajax.instances).forEach(function(instance) {
                if (instance.element_settings.wireId === wireInstanceId) {
                  instance.execute();
                }
              });
            }
          }
        }
      });
    },

    /**
     * Make textareas auto-resize as content is added.
     */
    initTextareaAutosize: function () {
      document.addEventListener('input', function (e) {
        if (e.target.matches('.wire-ai-chatbot-input textarea')) {
          e.target.style.height = 'auto';
          e.target.style.height = (e.target.scrollHeight) + 'px';
        }
      });
    },

    /**
     * Initialize scrolling to bottom of messages when new content arrives.
     */
    initMessageScrolling: function () {
      // This uses a MutationObserver to detect when new messages are added
      const messagesContainers = document.querySelectorAll('.wire-ai-chatbot-messages');
      
      if (messagesContainers.length) {
        const observer = new MutationObserver(function(mutations) {
          mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length) {
              const container = mutation.target;
              container.scrollTop = container.scrollHeight;
            }
          });
        });
        
        messagesContainers.forEach(function(container) {
          observer.observe(container, { childList: true, subtree: true });
          // Also scroll to bottom on initial load
          container.scrollTop = container.scrollHeight;
        });
      }
    }
  };

})(Drupal, drupalSettings, once);