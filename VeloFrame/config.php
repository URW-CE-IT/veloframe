<?php
define("DEBUG", 2);                         # 0: No Debug; 1: WARNings only; 2: WARNings and INFOrmational messages
define("ALLOW_INLINE_COMPONENTS", TRUE);    # Inline Component Processing could impact performance. To improve performance, you can disable it if its not needed.
define("AUTO_FIND_PAGES", TRUE);            # Automatically search for a pages directory next to the main running script and include all files inside.
define("AUTO_COMMENT_DEBUG", TRUE);         # Will print debug messages in HTML Comment Blocks to hide them in rendered DOM content
define("SERVICE_ATTRIB", TRUE);             # Will allow WebFramework to broadcast its usage using the X-Powered-By Header
define("AUTO_OPTIMIZE_CSS", TRUE);          # Automatically optimize CSS files by removing comments and whitespace. This will improve performance, but may break some CSS files that rely on comments or whitespace.
define("AUTO_OPTIMIZE_JS", TRUE);           # Automatically optimize CSS and JS files by removing comments and whitespace
define("AUTO_OPTIMIZE_IMAGES", TRUE);       # Automatically optimize images by removing metadata and compressing them
define("AUTO_OPTIMIZE_IMAGES_MAX_RESOLUTION", 1080); # Automatically optimize HTML files by removing comments and whitespace
define("CACHE_DIR", __DIR__ . "/cache/"); # Directory to store optimized files in. Make sure this directory is writable by the webserver user.