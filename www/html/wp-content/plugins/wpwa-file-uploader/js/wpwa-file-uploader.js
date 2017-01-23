;(function ($) {
  $(document).ready(function () {
    $('.wpwa_multi_file').each(function (idx, el) {
      var $el = $(el)
      var fieldId = $el.attr('id')
      var elId = 'wpwa_upload_panel_' + fieldId

      $el.after('<div id="' + elId + '"></div>')
      $('#' + elId).html('<input type="button" value="Add Files!" class="wpwa_upload_btn" id="' + fieldId + '">')
      $('#' + elId).append('<div class="wpwa_preview_box" id="' + fieldId + '_panel"></div>')
      $el.remove()
    })

    $('.wpwa_upload_btn').click(function (ev) {
      var $uploadObject = $(ev.currentTarget)

      wp.media.editor.send.attachment = function (props, attachment) {
        $uploadObject.parent()
          .find('.wpwa_preview_box')
          .append('<img class="wpwa_img_prev" style="width:75px;height:75px" src="' + attachment.url + '">')
        $uploadObject.parent()
          .find('.wpwa_preview_box')
          .append('<input class="wpwa_img_prev_hidden" type="hidden" name="h_' +
            $uploadObject.attr('id') + '[]" value="' + attachment.url + '">')
      }

      wp.media.editor.open()
      return false
    })

    var orgMedia = wp.media.editor.send.attachment
    $('.add_media').click(function () {
      wp.media.editor.send.attachment = orgMedia
    })

    $('body').on('drop', function () {
      wp.media.editor.send.attachment = orgMedia
    })

    $('body').on('dblclick', '.wpwa_img_prev', function (ev) {
      var $uploadedObject = $(this)
      $uploadedObject.next('.wpwa_img_prev_hidden').remove()
      $uploadedObject.remove()
    })
  })
})(jQuery.noConflict())
