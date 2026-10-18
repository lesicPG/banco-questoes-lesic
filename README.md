# Banco de Questões LESIC — v0.9

## Versão para incorporar ao Google Sites
Esta cópia mantém a estética LESIC no conteúdo central do banco, mas não inclui navegação/cabeçalho nem rodapé próprios, para evitar duplicação quando exibida dentro do Google Sites. Não requer migração do banco. Ao atualizar uma instalação, substitua os arquivos indicados abaixo e preserve os arquivos privados de configuração.

Para incorporar, a aplicação precisa estar publicada em um endereço HTTPS acessível aos visitantes. Um endereço local do XAMPP (`localhost`) funciona somente neste computador e não pode ser usado como endereço público da incorporação. No Google Sites, use **Inserir > Incorporar** ou **Páginas > Incorporar página inteira**, informe o endereço público do banco e publique as alterações. Alguns servidores podem bloquear a exibição dentro de iframe; nesse caso, a configuração de hospedagem precisa permitir a incorporação.

O pacote contém `config/google.example.php` para configuração local. Não distribua arquivos `config/google.php` ou outras cópias com credenciais; preserve os arquivos privados já existentes durante a atualização.

## GitHub e configurações privadas
O arquivo `.gitignore` exclui os arquivos locais de configuração, incluindo `config/database.php` e `config/google.php`. Para uma instalação nova, copie `config/database.example.php` para `config/database.php` e `config/google.example.php` para `config/google.php`, preenchendo-os apenas no servidor. Nunca publique credenciais, cópias de banco com perguntas reais, nem os arquivos privados de configuração.

Uma questão pode ser associada a vários jogos. Cadastro e edição permitem múltipla seleção; nenhuma seleção indica uso geral. Listagem e detalhes apresentam os jogos, e o filtro encontra a questão por qualquer associação, sem duplicar cartões. A referência bibliográfica é a última informação de cada cartão, abaixo dos jogos e do tipo.

## Atualização
Faça backup do banco e dos arquivos. Em instalação v0.8, selecione o banco existente e execute database/migrate_v0.8_to_v0.9.sql antes de copiar os novos arquivos. Preserve config/database.php e config/google.php. A migração cria question_games e copia as associações existentes, sem apagar ou duplicar perguntas. Pode ser reexecutada. schema.sql é somente para instalação nova.

Para usar esta cópia incorporável sobre a v0.9, substitua `public/index.php`, `public/detalhes.php`, `public/cadastrar.php`, `public/editar.php`, `public/login.php` e `public/css/style.css`; adicione `public/page-heading.php`. Remova os antigos `public/header.php` e `public/footer.php` caso estejam presentes. Não substitua `config` nem altere o banco.

O campo game_id permanece por compatibilidade com importações antigas; a aplicação usa todas as associações da nova tabela e a associação legada. Após importar um lote antigo, reexecutar a migração sincroniza a tabela. Para voltar ao código anterior, restaure os arquivos do backup; ele mostrará somente o jogo legado e não permitirá gerenciar os demais. Não exclua question_games.

Testados: preservação na migração, reexecução, criação/edição de vários jogos, filtros, uso geral, remoção de vínculos, rollback em jogo inválido e preservação de imagens. O lote Dental continua sem aplicação no banco real.

## Configuração do Google e demais instruções da v0.8

# Banco de Questões LESIC — v0.8

Acesso administrativo exclusivamente pela conta Google **lesic.utfpr.pg@gmail.com**. Consulta e detalhes continuam públicos. Cadastro, edição e exclusão exigem autenticação. O login por senha local foi substituído. Fonte, alternativas, categorias, jogos e relações WCAG/GAG foram preservados.

## Primeiro: configurar o Google

1. Entre no [Google Cloud Console](https://console.cloud.google.com/) com a conta responsável pelo LESIC e crie ou selecione um projeto para o Banco de Questões.
2. Abra **Google Auth Platform** e configure a identificação do aplicativo (Branding): nome “Banco de Questões LESIC”, e-mail de suporte e contato do responsável.
3. Em **Audience/Público-alvo**, use **External/Externo** para a conta Gmail. Durante os testes, adicione `lesic.utfpr.pg@gmail.com` como usuário de teste. A restrição administrativa também é aplicada pelo código, independentemente dessa lista.
4. Em **Clients/Clientes**, crie um cliente OAuth do tipo **Web application/Aplicativo da Web**.
5. Adicione exatamente este URI em **Authorized redirect URIs/URIs de redirecionamento autorizados**:

```
http://localhost/banco-questoes-lesic/public/google-retorno.php
```

6. Guarde o **Client ID/ID do cliente** e o **Client secret/Chave secreta do cliente**. Copie `config/google.example.php` para `config/google.php` e preencha `client_id` e `client_secret` no seu computador. Preserve o endereço de retorno acima. Não envie a chave secreta no chat nem a publique.

O fluxo solicita apenas `openid email`, para confirmar a identidade e o e-mail. Não solicita acesso a mensagens do Gmail. Não é necessário configurar uma senha no Banco de Questões.

## Instalar sobre a v0.6 ou v0.7

1. Faça backup da pasta instalada e do banco existente.
2. Prepare `config/google.php` conforme as instruções acima antes de trocar a versão em uso.
3. Copie `public`, `src` e `tools` desta versão para `C:\xampp\htdocs\banco-questoes-lesic`, aceitando substituir os arquivos. Copie também `config/google.php` e `config/.htaccess`. **Preserve o seu `config/database.php`**. Não copie a pasta v0.8 inteira para dentro da aplicação.
4. Não execute SQL novo: v0.6/v0.7 já têm a coluna Fonte. Não reimporte `schema.sql` nem os lotes de questões. Para instalações v0.5.1, aplique antes a migração de Fonte incluída no diretório database.
5. Atualize o site com Ctrl+F5, clique em **Entrar como administrador**, depois em **Entrar com Google**, e selecione `lesic.utfpr.pg@gmail.com`.
6. Confirme que os botões administrativos aparecem. Ao clicar em **Sair**, eles devem desaparecer. Outras contas Google não recebem acesso administrativo.

A aplicação usa uma nova sessão, para que um login por senha de versões anteriores não seja aceito. `config/admin.php` e o controle de tentativas antigo não são mais utilizados; podem permanecer no backup. O configurador antigo em tools foi desativado para atualizações por cópia. Não execute `configurar-admin.ps1` da versão anterior.

## Instalação nova

PHP 7.4+ com PDO MySQL, cURL e OpenSSL; MySQL/MariaDB. Configure `config/database.php` e importe `database/schema.sql` somente em banco novo. Depois configure Google conforme acima. Não há dependências Composer.

## Hospedagem com o Edu

Use HTTPS, defina `public` como raiz do site e cadastre no Google o URI real de retorno (ex.: o endereço público terminado em `/google-retorno.php`). Atualize `redirect_uri` em `config/google.php` para o mesmo endereço exato. Não derive esse endereço de informações enviadas pelo navegador. Preserve os arquivos de configuração privados, fora da raiz pública. Configure certificados de confiança para cURL/OpenSSL e a pasta de sessões do PHP. A sessão expira após 30 minutos sem atividade. Sair encerra a sessão do LESIC, sem desconectar a conta Google dos demais serviços.

## Problemas comuns

- **redirect_uri_mismatch**: o endereço de retorno no Google e em `config/google.php` precisa ser idêntico, incluindo protocolo, pasta e nome do arquivo.
- **Acesso Google ainda não configurado**: confira se `config/google.php` existe e se os três campos estão preenchidos.
- **Conta sem permissão**: use exatamente a conta autorizada. Durante testes, confira também o usuário de teste no Google Cloud.
- **Não foi possível confirmar o acesso com o Google**: confira conexão com a internet, credenciais, extensões cURL/OpenSSL e certificados de confiança do PHP. Não desative a verificação TLS.
- **Solicitação expirada ou inválida**: volte ao login e inicie novamente; o retorno deve ocorrer no mesmo navegador, com cookies habilitados, em até dez minutos.

## Verificação e limites desta entrega

Todos os arquivos PHP passaram na verificação de sintaxe. Testes automatizados confirmaram assinatura, rejeição de outra conta, e-mail não verificado, emissor/destinatário incorreto, expiração, nonce, state e repetição de retorno; também sessão antiga e expiração por inatividade. Testes HTTP locais cobriram consulta pública, proteção das rotas, configuração ausente, início do fluxo Google, CSRF, cancelamento e retorno inválido. Nenhuma questão foi alterada.

**O login real com Google ainda depende das credenciais do seu projeto e deve ser testado após configurá-las. Esta entrega não foi instalada na pasta do XAMPP.** Não há credenciais Google neste pacote.

Teste de validação: `php tests/google-auth.php` (somente CLI, com chaves de teste geradas em memória).

Referência de implementação e configuração: [Google OpenID Connect](https://developers.google.com/identity/openid-connect/openid-connect).

