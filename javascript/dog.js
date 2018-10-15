(function($){
  const css = `
  div.field.dropdown, div.field.text { width: calc(50% - 3em); }
  div.mykey { float: none; }
  div.myvalue { float: none; }
  div.myvalue { margin-left: 40px; }
  .btn-remove { margin-left: 1em; }
  .btn-add, .btn-remove { cursor: pointer; height: 2.5em; color: #fff; text-shadow: 1px 1px rgba(0,0,0,.5); border-width: 1px; border-style: solid; border-radius: 4px; box-shadow: 1px 1px 2px rgba(255,255,255,.4) inset, -1px -1px 2px rgba(0,0,0,.3) inset; }
  .btn-add > .material-icons, .btn-remove > .material-icons { vertical-align: middle; }
  .btn-add { background: #3c6; border-color: #afc #063 #063 #afc; }
  .btn-remove { background: #f66; border-color: #faa #633 #633 #faa; }
  .InnerComposite { display: none; }
  .InnerComposite.active { display: flex; }
  `;

  $(function(){
      $('<style>', { text: css }).appendTo(document.head);

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
          $(this).children('.InnerComposite').each(function(i){
              $('<button type="button" class="btn-remove" title="Remove"><i class="material-icons">remove_circle_outline</i></button>')
                  .appendTo(this)
                  .click(
                      i,
                      function(e){
                          // TODO: use front-end framework e.g. React, Angular, Vue, Ember
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
