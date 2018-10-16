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
      $(this).sortable({
        start: function(evt, ui){
          $(ui.item).addClass('grabbing');
        },
        stop: function(){
          $(this).children('.InnerComposite').removeClass('grabbing');
        },
        update: function(){
          function rewriteName(e, i){
            i++;
            $.each(['name', 'id'], function(){
              let attr = e.attr(this);
              if (attr) {
                attr = attr.replace(/\d+/, i);
                e.attr(this, attr);
              }
            });
          }
          $(this).children('.InnerComposite').each(function(i, e){
            rewriteName($(e).find('.mykey:input'), i);
            rewriteName($(e).find('.myvalue:input'), i);
          });
        }
      });
      $(this).children('.InnerComposite').each(function(i){
        $('<button type="button" class="btn-remove" title="Remove" tabindex="-1"><i class="material-icons">remove_circle_outline</i></button>')
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
              array.forEach(function(i, e){
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
            $(e.data).find('.InnerComposite:not(.active):first').addClass('active').find('input').focus();
            showHideAddButton(e.data);
          });
    });
  });
})(jQuery);
