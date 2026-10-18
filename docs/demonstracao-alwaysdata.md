# Demonstração no alwaysdata

Este roteiro publica uma cópia de demonstração do Banco de Questões LESIC. O pacote usa PHP e MariaDB/MySQL, disponíveis no alwaysdata Free. Consulte a [oferta atual](https://www.alwaysdata.com/en/offers/) antes de criar a conta, pois limites e condições podem mudar.

## 1. Criar a conta e o site

1. Crie a conta alwaysdata e use o endereço gratuito `NOME.alwaysdata.net`.
2. No painel, crie um banco MariaDB/MySQL em **Databases > MySQL** e um usuário com permissões sobre esse banco.
3. Em **Web > Sites**, adicione o endereço `NOME.alwaysdata.net`, selecione o tipo **PHP** e a raiz pública da aplicação. Se os arquivos forem enviados para `www/banco-questoes/public`, defina esse diretório como raiz do site.
4. Ative HTTPS para o endereço gratuito, conforme as opções SSL do painel.

No alwaysdata, o servidor MySQL é informado no painel e segue o formato `mysql-NOME.alwaysdata.net`; não use `127.0.0.1` como host do banco remoto. Consulte [MariaDB/MySQL](https://help.alwaysdata.com/en/docs/web-hosting/databases/mariadb/), [configuração PHP](https://help.alwaysdata.com/en/docs/web-hosting/languages/php/configuration/) e [criação de sites](https://help.alwaysdata.com/en/docs/web-hosting/sites/add-a-site/).

## 2. Enviar os arquivos

Envie a pasta do projeto para `www/banco-questoes`, mantendo `public`, `src`, `config` e `database` como pastas irmãs. A raiz pública do site deve apontar para `www/banco-questoes/public`, para que `config` e `src` fiquem fora da pasta acessível pela Web.

Use SFTP pelo painel/cliente de sua preferência. Não publique arquivos com `client_secret` ou senhas. Preserve `config/.htaccess` (nega acesso Web ao diretório de configuração).

## 3. Configurar o banco e importar os dados

1. Configure as variáveis de ambiente do site no painel: `LESIC_DB_HOST=mysql-lesic.alwaysdata.net`, `LESIC_DB_PORT=3306`, `LESIC_DB_NAME=lesic_banco_questoes`, `LESIC_DB_USER=lesic`, `LESIC_DB_CHARSET=utf8mb4` e `LESIC_DB_PASSWORD` com a senha do usuário do banco. A senha deve ser preenchida diretamente no painel; não a coloque no repositório nem no chat. O `config/database.php` mantém valores locais padrão para o XAMPP e lê essas variáveis quando estão definidas.
2. Exporte do phpMyAdmin local uma cópia SQL do banco que será usada na demonstração. Importe-a no banco remoto pelo phpMyAdmin do alwaysdata.
3. Confirme que a cópia inclui tabelas, perguntas, alternativas, relações WCAG/GAG e eventuais imagens armazenadas no banco. Não aplique `schema.sql` sobre uma cópia que já contém dados.

O banco MariaDB/MySQL remoto usa o nome de host e credenciais próprios da conta; a documentação do alwaysdata indica onde encontrá-los no painel.

## 4. Configurar o login Google

1. Duplique `config/google.example.php` como `config/google.php` no servidor e informe o Client ID e o Client Secret já usados pelo LESIC.
2. Se a raiz pública apontar para `www/banco-questoes/public`, use como endereço de retorno exato:

   `https://NOME.alwaysdata.net/google-retorno.php`

3. Cadastre esse mesmo endereço em **Authorized redirect URIs** no cliente OAuth do Google e no `redirect_uri` de `config/google.php`.

Não envie nem cole Client Secret, senha do banco ou chave privada no chat. Se as credenciais OAuth existentes não puderem ser reutilizadas, o responsável pela conta Google terá de criar/autorizar um cliente apropriado.

## 5. Incorporar ao Google Sites

Use a URL pública HTTPS da aplicação como página incorporada. O Google Sites também permite inserir uma página completa. Faça primeiro uma prévia sem publicar; se aparecer bloqueio de incorporação, revise os cabeçalhos HTTP do site (por exemplo, `X-Frame-Options` e `Content-Security-Policy: frame-ancestors`) e permita somente a origem do site LESIC.

O Google Sites alerta que alguns sites externos podem não permitir incorporação. Por isso, confirme a prévia e o login antes de publicar a página.

## Antes da demonstração

- Use uma cópia separada do banco para preservar o XAMPP.
- Teste consulta, filtros, detalhes e imagens na URL HTTPS.
- Teste o login Google e o endereço de retorno.
- Teste a incorporação na prévia do Google Sites; só publique depois de confirmar com o responsável pelo site.

