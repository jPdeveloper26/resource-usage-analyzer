/**
 * Admin JavaScript for Resource Usage Analyzer
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        var scanForm = $('#rua-scan-form');
        var scanButton = $('#rua-scan-button');
        var progressBar = $('#rua-progress');
        var progressFill = $('.rua-progress-fill');
        var progressText = $('.rua-progress-text');
        var resultsSection = $('#rua-results');
        var resultsContent = $('#rua-results-content');
        var recommendationsSection = $('#rua-recommendations');
        var recommendationsContent = $('#rua-recommendations-content');

        // Handle scan form submission
        scanForm.on('submit', function(e) {
            e.preventDefault();

            // Disable button and show progress
            scanButton.prop('disabled', true).addClass('scanning');
            scanButton.html(resource_usage_analyzer_ajax.strings.scanning + ' <span class="rua-spinner"></span>');
            progressBar.show();
            resultsSection.hide();
            recommendationsSection.hide();

            // Update progress
            var progress = 0;
            var progressInterval = setInterval(function() {
                progress += Math.random() * 15;
                if (progress > 90) {
                    progress = 90;
                }
                updateProgress(progress);
            }, 500);

            // Perform AJAX request
            $.ajax({
                url: resource_usage_analyzer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resource_usage_analyzer_scan',
                    nonce: resource_usage_analyzer_ajax.nonce
                },
                success: function(response) {
                    clearInterval(progressInterval);
                    updateProgress(100);

                    if (response.success) {
                        progressText.text(resource_usage_analyzer_ajax.strings.complete);
                        
                        // Get and display report
                        getReport();
                    } else {
                        showError(response.data.message || resource_usage_analyzer_ajax.strings.error);
                        resetScanButton();
                    }
                },
                error: function() {
                    clearInterval(progressInterval);
                    showError(resource_usage_analyzer_ajax.strings.error);
                    resetScanButton();
                }
            });
        });

        // Get report after scan
        function getReport() {
            $.ajax({
                url: resource_usage_analyzer_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resource_usage_analyzer_get_report',
                    nonce: resource_usage_analyzer_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        displayResults(response.data);
                        resetScanButton();
                        
                        // Add export functionality
                        addExportButton(response.data.data);
                    } else {
                        showError(response.data.message || resource_usage_analyzer_ajax.strings.error);
                        resetScanButton();
                    }
                },
                error: function() {
                    showError(resource_usage_analyzer_ajax.strings.error);
                    resetScanButton();
                }
            });
        }

        // Display results
        function displayResults(data) {
            resultsContent.html(data.report);
            resultsSection.fadeIn();
            
            if (data.recommendations) {
                recommendationsContent.html(data.recommendations);
                recommendationsSection.fadeIn();
            }
            
            // Animate stats
            animateStats();
        }

        // Animate statistics
        function animateStats() {
            $('.rua-stat-value').each(function() {
                var $this = $(this);
                var value = $this.text();
                var isNumber = !isNaN(value) && !isNaN(parseFloat(value));
                
                if (isNumber) {
                    var finalValue = parseInt(value);
                    $this.text('0');
                    
                    $({ counter: 0 }).animate({ counter: finalValue }, {
                        duration: 1000,
                        easing: 'swing',
                        step: function() {
                            $this.text(Math.ceil(this.counter));
                        },
                        complete: function() {
                            $this.text(finalValue);
                        }
                    });
                }
            });
        }

        // Update progress bar
        function updateProgress(percent) {
            progressFill.css('width', percent + '%');
            progressText.text(Math.round(percent) + '%');
        }

        // Reset scan button
        function resetScanButton() {
            scanButton.prop('disabled', false).removeClass('scanning');
            scanButton.text(scanButton.data('original-text') || 'Start Analysis');
            progressBar.fadeOut();
        }

        // Show error message
        function showError(message) {
            var errorHtml = '<div class="notice notice-error"><p>' + message + '</p></div>';
            scanForm.after(errorHtml);
            
            setTimeout(function() {
                $('.notice-error').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }

        // Add export button
        function addExportButton(data) {
            var exportButton = $('<button>', {
                'class': 'button button-secondary rua-export-button',
                'text': 'Export Results',
                'click': function() {
                    exportResults(data);
                }
            });
            
            if (!$('.rua-export-button').length) {
                resultsSection.find('h2').after(exportButton);
            }
        }

        // Export results
        function exportResults(data) {
            var exportData = {
                generated: new Date().toISOString(),
                site_url: window.location.hostname,
                ...data
            };
            
            var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(exportData, null, 2));
            var downloadAnchor = document.createElement('a');
            downloadAnchor.setAttribute("href", dataStr);
            downloadAnchor.setAttribute("download", "resource-usage-analysis-" + Date.now() + ".json");
            document.body.appendChild(downloadAnchor);
            downloadAnchor.click();
            downloadAnchor.remove();
        }

        // Store original button text
        scanButton.data('original-text', scanButton.text());

        // Handle expandable details
        $(document).on('click', '.rua-expand-details', function(e) {
            e.preventDefault();
            var $this = $(this);
            var $details = $this.closest('tr').next('.rua-details-row');
            
            if ($details.is(':visible')) {
                $details.fadeOut();
                $this.text('+');
            } else {
                $details.fadeIn();
                $this.text('-');
            }
        });

        // Handle recommendation dismissal
        $(document).on('click', '.rua-dismiss-recommendation', function(e) {
            e.preventDefault();
            $(this).closest('.rua-recommendation').fadeOut();
        });
    });

})(jQuery);