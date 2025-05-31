<?php
define("DEBUG", 2);                         # 0: No Debug; 1: WARNings only; 2: WARNings and INFOrmational messages
define("ALLOW_INLINE_COMPONENTS", TRUE);    # Inline Component Processing could impact performance. To improve performance, you can disable it if its not needed.
define("AUTO_FIND_PAGES", TRUE);            # Automatically search for a pages directory next to the main running script and include all files inside.
define("AUTO_COMMENT_DEBUG", TRUE);         # Will print debug messages in HTML Comment Blocks to hide them in rendered DOM content
define("SERVICE_ATTRIB, TRUE);              # Will allow WebFramework to broadcast its usage using the X-Powered-By Header
