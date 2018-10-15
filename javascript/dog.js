(function($){
  $(function(){
    $('.InnerComposite').filter(function(){
      let hasAny = false;
      $(this).find('.mykey:input, .myvalue:input').each(function(){
          hasAny |= !!$(this).val();
      });
      return hasAny;
    }).addClass('active');

    function showHideAddButton(outer){
      let remaining = $(outer).find('.InnerComposite:not(.active):first');
      $(outer).find('.btn-add').toggle(remaining.length > 0);
    };

    $('.OuterComposite.multiple').each(function(){
      $(this).sortable();
      $(this).children('.InnerComposite').each(function(i){
        $('<button type="button" class="btn-remove" title="Remove"><i class="material-icons">remove_circle_outline</i></button>')
          .appendTo(this)
          .click(
            i,
            function(e){
              let pos = e.data;
              let me = $(this).closest('.InnerComposite');
              let parent = me.closest('.OuterComposite');
              let children = parent.find('.InnerComposite');
              //console.log(pos, me, parent);
              let array = [];
              children.each(function(){
                if ($(this).hasClass('active')) {
                  array.push({ key: $(this).find('.mykey:input').val(), value: $(this).find('.myvalue:input').val() });
                }
              });
              array.splice(pos, 1);
              array.forEach(function(e, i){
                let c = children.eq(i);
                c.find('.mykey:input').val(e.key);
                c.find('.myvalue:input').val(e.value);
              });
              //console.log(array);
              for (let i = array.length, len = children.length; i < len; i++) {
                let c = children.eq(i);
                c.find('.mykey:input').val('');
                c.find('.myvalue:input').val('');
                c.removeClass('active');
              }
              showHideAddButton(parent);
            });
        });
    });

    $('.OuterComposite.multiple').each(function(){
      $('<button type="button" class="btn-add" title="Add"><i class="material-icons">add_circle_outline</i></button>')
        .appendTo(this)
        .click(
          this,
          function(e){
            $(e.data).find('.InnerComposite:not(.active):first').addClass('active');
            showHideAddButton(e.data);
          });
    });

    $('select[name="Template"]').change(
      function(e){
        var selection = $(this).val();
        if (typeof selection === 'string' && selection.length > 0) {
          if (confirm('Loading a template will remove any data you have entered already.  Would you like to continue?')) {
            // Save it!
            var url = '$LoadTemplateLink' + selection;
            window.location = url;
            $('body').fadeOut();
          } else {
            // Do nothing!
          }
        }
      });
  });
})(jQuery);
