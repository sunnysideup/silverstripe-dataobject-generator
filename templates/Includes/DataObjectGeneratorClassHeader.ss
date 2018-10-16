<% if $NameSpace %>
namespace $NameSpace;
<% end_if %>

use SilverStripe\\Security\\Permission;
<% if $casting %>
use SilverStripe\\ORM\\FieldType\\DBField
<% end_if %>

<% if $FinalListToUse %>
<% loop $FinalListToUse %>
use $FullClassName;
<% end_loop %>
<% end_if %>
