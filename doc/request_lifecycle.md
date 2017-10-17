<!-- MarkdownTOC list_bullets="*" bracket="round" lowercase="true" autolink="true" indent="" -->

* [1. Request lifecycle](#1-request-lifecycle)

<!-- /MarkdownTOC -->

# 1. Request lifecycle
  1.  Receive request from Client browser to Apache
  2.  Apache loads PHP and runs "bootstrap.php"
  3.  "bootstrap.php" creates $app = new Spin();
      * BOOTSTRAP PHASE:
        - Register Framework Global Helper Functions
        - Load Config
        - Load Factories
          * Cache Factory
          * HTTP Factory
          * Container Factory
          * Event Factory
          * Connections Factory
        - Load Hook Manager
        - Create HTTP Server Request, Response
          * Populate Server Request with data

  4.  "bootstrap.php" code:
        - Register "User" Global Functions        

  5.  "bootstrap.php" calls $app->run();
      * PRE-PROCESS PHASE:
        - Framework Hooks (onBeforeRequest)
          * Load & Create Hooks one by one
          * Foreach Hook call $hook->run(); if == false, terminate
        - PROCESS PHASE:
          * Match Route
            - Execute Global Before Middlewares
            - Execute Route Specific Before Middlewares
            - Load & Call Controller->handle()
            - Execute Route Specific After Middlewares
            - Execute Global After Middlewares
        - POST-PROCESS PHASE:
          * Framework Hooks (onAfterRequest)
            - Load & Create Hooks one by one
            - Foreach Hook call $hook->run(); if == false, terminate

  6.  Send response to Client
