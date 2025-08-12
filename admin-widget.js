jQuery(document).ready(function($) {
  function evaluateCode() {
    const code = $('#wp-http-eval-code').val();
    const resultDiv = $('#wp-http-eval-result');

    if (!code) return;

    resultDiv.hide().empty();

    $.ajax({
      url: wpHttpEval.ajaxUrl + '?action=wp_http_eval&_ajax_nonce=' + wpHttpEval.nonce,
      type: 'POST',
      data: code,
      contentType: 'text/plain',
      processData: false,
      beforeSend: function() {
        $('#wp-http-eval-submit').prop('disabled', true).text('Evaluating...');
      },
      success: function(response) {
        if (response.success) {
          resultDiv.html('<strong>Result:</strong><pre>' + $('<div>').text(response.result).html() + '</pre>');
        } else {
          // Handle WordPress error format
          const errorMessage = response.errors
            ? Object.values(response.errors)[0][0]
            : response.data?.message || 'Unknown error';
          resultDiv.html('<strong>Error:</strong> ' + errorMessage);
        }
      },
      error: function(xhr) {
        const response = xhr.responseJSON || {};
        const errorMessage = response.errors
          ? Object.values(response.errors)[0][0]
          : response.data?.message || 'Unknown error occurred';
        resultDiv.html('<strong>Error:</strong> ' + errorMessage);
      },
      complete: function() {
        $('#wp-http-eval-submit').prop('disabled', false).text('Evaluate');
        resultDiv.show();
      }
    });
  }

  $('#wp-http-eval-submit').click(evaluateCode);

  $('#wp-http-eval-code').on('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
      evaluateCode();
      e.preventDefault();
    }

  });
});
