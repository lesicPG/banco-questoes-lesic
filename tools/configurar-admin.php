<?php
// Desativa o configurador de senha de versões anteriores, inclusive em atualização por cópia.
if(PHP_SAPI!=='cli')http_response_code(404);
exit('Esta versão usa exclusivamente o acesso Google. Consulte o README.');

