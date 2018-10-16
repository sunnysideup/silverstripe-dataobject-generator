<% if $NameSpace %>
namespace $NameSpace;
<% end_if %>


<% if $FinalListToUse %>
<% loop $FinalListToUse %>
use $FullClassName;
<% end_loop %>
<% end_if %>
