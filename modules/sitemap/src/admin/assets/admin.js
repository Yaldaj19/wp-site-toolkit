/**
 * Admin JavaScript
 * 
 * Related Files:
 * - admin/class-admin.php
 * - admin/views/dashboard.php
 * - admin/assets/admin-layout.css
 * - admin/assets/admin-components.css
 */

(function($) {
  'use strict';

  //-------------------------------
  // Document Ready
  $(document).ready(function() {
      initToggleSettings();
      initFormSubmit();
      initClearCache();
      initRebuild();
      initPing();
      initExport();
      initImport();
      initModal();
  });

  //-------------------------------
  // Toggle Settings Panel
  function initToggleSettings() {
      $('.ssg-toggle-settings').on('click', function(e) {
          e.preventDefault();
          
          var $button = $(this);
          var $card = $button.closest('.ssg-item-card');
          var $settings = $card.find('.ssg-item-settings');
          
          $settings.slideToggle(250);
      });
  }

  //-------------------------------
  // Form Submit via AJAX
  function initFormSubmit() {
      $('#ssg-form').on('submit', function(e) {
          e.preventDefault();
          
          var $form = $(this);
          var $button = $form.find('button[type="submit"]');
          var originalText = $button.html();
          
          // Show loading
          $button.prop('disabled', true);
          $button.html(ssgAdmin.strings.saving + ' <span class="ssg-loading"></span>');
          
          // Send AJAX
          $.ajax({
              url: ssgAdmin.ajaxUrl,
              type: 'POST',
              data: {
                  action: 'ssg_save',
                  nonce: ssgAdmin.nonce,
                  settings: $form.serialize()
              },
              success: function(response) {
                  if (response.success) {
                      showMessage(ssgAdmin.strings.saved, 'success');
                  } else {
                      showMessage(response.data.message || ssgAdmin.strings.error, 'error');
                  }
              },
              error: function() {
                  showMessage(ssgAdmin.strings.error, 'error');
              },
              complete: function() {
                  $button.prop('disabled', false);
                  $button.html(originalText);
              }
          });
      });
  }

  //-------------------------------
  // Clear Cache Button
  function initClearCache() {
      $('#ssg-clear-cache').on('click', function(e) {
          e.preventDefault();
          
          var $button = $(this);
          var originalText = $button.html();
          
          // Show loading
          $button.prop('disabled', true);
          $button.html(ssgAdmin.strings.clearing + ' <span class="ssg-loading"></span>');
          
          // Send AJAX
          $.ajax({
              url: ssgAdmin.ajaxUrl,
              type: 'POST',
              data: {
                  action: 'ssg_clear_cache',
                  nonce: ssgAdmin.nonce
              },
              success: function(response) {
                  if (response.success) {
                      showMessage(ssgAdmin.strings.cleared, 'success');
                  } else {
                      showMessage(response.data.message || ssgAdmin.strings.error, 'error');
                  }
              },
              error: function() {
                  showMessage(ssgAdmin.strings.error, 'error');
              },
              complete: function() {
                  $button.prop('disabled', false);
                  $button.html(originalText);
              }
          });
      });
  }

  //-------------------------------
  // Rebuild Sitemap Button
  function initRebuild() {
      $('#ssg-rebuild').on('click', function(e) {
          e.preventDefault();
          
          var $button = $(this);
          var originalText = $button.html();
          
          // Show loading
          $button.prop('disabled', true);
          $button.html(ssgAdmin.strings.rebuilding + ' <span class="ssg-loading"></span>');
          
          // Send AJAX
          $.ajax({
              url: ssgAdmin.ajaxUrl,
              type: 'POST',
              data: {
                  action: 'ssg_rebuild',
                  nonce: ssgAdmin.nonce
              },
              success: function(response) {
                  if (response.success) {
                      showMessage(ssgAdmin.strings.rebuilt, 'success');
                  } else {
                      showMessage(response.data.message || ssgAdmin.strings.error, 'error');
                  }
              },
              error: function() {
                  showMessage(ssgAdmin.strings.error, 'error');
              },
              complete: function() {
                  $button.prop('disabled', false);
                  $button.html(originalText);
              }
          });
      });
  }

  //-------------------------------
  // Ping Search Engines Button
  function initPing() {
      $('#ssg-ping').on('click', function(e) {
          e.preventDefault();
          
          var $button = $(this);
          var originalText = $button.html();
          
          // Show loading
          $button.prop('disabled', true);
          $button.html(ssgAdmin.strings.pinging + ' <span class="ssg-loading"></span>');
          
          // Send AJAX
          $.ajax({
              url: ssgAdmin.ajaxUrl,
              type: 'POST',
              data: {
                  action: 'ssg_ping',
                  nonce: ssgAdmin.nonce
              },
              success: function(response) {
                  if (response.success) {
                      showMessage(ssgAdmin.strings.pinged, 'success');
                  } else {
                      showMessage(response.data.message || ssgAdmin.strings.error, 'error');
                  }
              },
              error: function() {
                  showMessage(ssgAdmin.strings.error, 'error');
              },
              complete: function() {
                  $button.prop('disabled', false);
                  $button.html(originalText);
              }
          });
      });
  }

  //-------------------------------
  // Export Settings Button
  function initExport() {
      $('#ssg-export').on('click', function(e) {
          e.preventDefault();
          
          var $button = $(this);
          
          // Send AJAX
          $.ajax({
              url: ssgAdmin.ajaxUrl,
              type: 'POST',
              data: {
                  action: 'ssg_export',
                  nonce: ssgAdmin.nonce
              },
              success: function(response) {
                  if (response.success) {
                      // Create download link
                      var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(response.data.data);
                      var downloadAnchorNode = document.createElement('a');
                      downloadAnchorNode.setAttribute("href", dataStr);
                      downloadAnchorNode.setAttribute("download", "ssg-sitemap-settings.json");
                      document.body.appendChild(downloadAnchorNode);
                      downloadAnchorNode.click();
                      downloadAnchorNode.remove();
                      
                      showMessage('تنظیمات با موفقیت دانلود شد!', 'success');
                  } else {
                      showMessage(response.data.message || ssgAdmin.strings.error, 'error');
                  }
              },
              error: function() {
                  showMessage(ssgAdmin.strings.error, 'error');
              }
          });
      });
  }

  //-------------------------------
  // Import Settings Button
  function initImport() {
      $('#ssg-import').on('click', function(e) {
          e.preventDefault();
          $('#ssg-import-modal').fadeIn(200);
      });
      
      $('#ssg-import-confirm').on('click', function(e) {
          e.preventDefault();
          
          var json = $('#ssg-import-data').val().trim();
          
          if (!json) {
              alert('لطفاً داده JSON را وارد کنید.');
              return;
          }
          
          var $button = $(this);
          var originalText = $button.html();
          
          // Show loading
          $button.prop('disabled', true);
          $button.html('در حال وارد کردن... <span class="ssg-loading"></span>');
          
          // Send AJAX
          $.ajax({
              url: ssgAdmin.ajaxUrl,
              type: 'POST',
              data: {
                  action: 'ssg_import',
                  nonce: ssgAdmin.nonce,
                  json: json
              },
              success: function(response) {
                  if (response.success) {
                      showMessage('تنظیمات با موفقیت وارد شد!', 'success');
                      $('#ssg-import-modal').fadeOut(200);
                      $('#ssg-import-data').val('');
                      
                      // Reload page after 1 second
                      setTimeout(function() {
                          location.reload();
                      }, 1000);
                  } else {
                      showMessage(response.data.message || ssgAdmin.strings.error, 'error');
                  }
              },
              error: function() {
                  showMessage(ssgAdmin.strings.error, 'error');
              },
              complete: function() {
                  $button.prop('disabled', false);
                  $button.html(originalText);
              }
          });
      });
  }

  //-------------------------------
  // Modal Controls
  function initModal() {
      $('.ssg-modal-close').on('click', function() {
          $(this).closest('.ssg-modal').fadeOut(200);
      });
      
      $(window).on('click', function(e) {
          if ($(e.target).hasClass('ssg-modal')) {
              $(e.target).fadeOut(200);
          }
      });
  }

  //-------------------------------
  // Show Message
  function showMessage(message, type) {
      var $messageBox = $('#ssg-message');
      
      $messageBox
          .removeClass('notice-success notice-error')
          .addClass('notice-' + type)
          .html('<p>' + message + '</p>')
          .slideDown(300);
      
      setTimeout(function() {
          $messageBox.slideUp(300);
      }, 3000);
  }

})(jQuery);