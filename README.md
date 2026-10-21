# Banco de Questões LESIC

Aplicação web para consultar, filtrar e administrar questões relacionadas a jogos educacionais. A interface desta versão foi preparada para ser incorporada ao Google Sites: apresenta o conteúdo principal sem repetir o cabeçalho e o rodapé do site hospedeiro.

## Recursos

- Consulta pública de questões e visualização de detalhes.
- Pesquisa e filtros por categoria, jogo, tipo e outros campos disponíveis.
- Cadastro e edição administrativa protegidos por autenticação Google.
- Associação de uma questão a um ou mais jogos, incluindo questões de uso geral.
- Organização de categorias, alternativas, fontes e diretrizes de acessibilidade.
- Layout responsivo para telas menores.

## Requisitos

- PHP 7.4 ou superior, com PDO MySQL, cURL e OpenSSL habilitados.
- MySQL ou MariaDB.
- HTTPS recomendado para publicação; necessário para a configuração OAuth em produção.

Não há dependências Composer.

## Instalação

1. Publique os diretórios da aplicação em um servidor PHP e configure `public` como raiz pública do site.
2. Copie `config/database.example.php` para `config/database.php` e preencha os dados de conexão ao banco.
3. Em um banco novo, importe `database/schema.sql`.
4. Configure o login Google conforme a próxima seção.
5. Acesse o endereço público e confirme a consulta das questões e o login administrativo.

Não importe `schema.sql` sobre um banco que já contém dados. Para atualizar uma instalação existente, faça backup dos arquivos e do banco e siga as instruções de migração correspondentes à versão instalada.

## Login administrativo com Google

1. Crie um cliente OAuth do tipo **Aplicativo da Web** no Google Cloud.
2. Cadastre como URI de redirecionamento autorizado o endereço HTTPS completo da aplicação terminado em `/google-retorno.php` (por exemplo, `https://seu-dominio.example/google-retorno.php`). O endereço deve corresponder exatamente ao usado pela aplicação.
3. Copie `config/google.example.php` para `config/google.php` e configure nele o Client ID, o Client Secret, o endereço de retorno e a conta autorizada para administração.
4. Mantenha `config/google.php` fora da publicação do repositório e da raiz pública sempre que a configuração do servidor permitir.

O fluxo de autenticação usa OpenID Connect para confirmar a identidade. Nunca publique ou compartilhe o Client Secret ou outros arquivos de configuração preenchidos.

## Incorporação ao Google Sites

1. Publique a aplicação em um endereço HTTPS acessível aos visitantes.
2. No Google Sites, use **Inserir > Incorporar** ou **Páginas > Incorporar página inteira** e informe o endereço público do banco.
3. Publique o site e confira a página em uma janela sem autenticação administrativa.

Se o conteúdo não aparecer dentro do Google Sites, verifique se a hospedagem permite exibição em `iframe` e se as políticas de segurança não bloqueiam a incorporação.

## Estrutura do projeto

- `public/`: páginas e recursos servidos pelo site.
- `src/`: lógica da aplicação.
- `config/`: exemplos de configuração; arquivos com credenciais devem permanecer privados.
- `database/`: estrutura inicial e migrações do banco.
- `docs/`: documentação complementar.
- `tests/`: verificações automatizadas do projeto.
- `tools/`: utilitários de apoio.

## Segurança e dados

- Não adicione ao repositório arquivos de configuração preenchidos, credenciais, cópias do banco ou exportações SQL com dados reais.
- Antes de publicar ou atualizar, revise a visibilidade do repositório e faça backup do banco e dos arquivos de configuração.
- Use HTTPS e mantenha PHP, MySQL/MariaDB e o servidor atualizados.

## Créditos

Desenvolvido por Antony Goes para o LESIC — Laboratório de Engenharia de Software e Inteligência Computacional.
