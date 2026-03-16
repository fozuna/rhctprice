# Instalação Web (sem SSH)

Este projeto possui instalador via navegador em `https://seu-dominio/install.php`.

## Antes de iniciar

- Faça upload dos arquivos do projeto no servidor.
- Garanta que o domínio aponta para a pasta `public` ou para a raiz com rewrite ativo.
- Tenha em mãos:
  - DSN do banco MySQL
  - usuário e senha do banco
  - e-mails de envio (RH e remetente)
  - credenciais do supervisor e admin inicial

## Como instalar

1. Abra `https://seu-dominio/install.php`.
2. Verifique os requisitos exibidos na tela.
3. Preencha os campos do formulário.
4. Clique em **Instalar agora**.
5. Aguarde o log finalizar com sucesso.
6. Se seu provedor configurar `INSTALLER_KEY`, informe a chave para liberar a execução.

## O que o instalador faz

- Valida requisitos do servidor.
- Cria `app/config/local.php` com parâmetros de produção.
- Conecta no banco.
- Importa `database/schema.sql`.
- Executa migrações incrementais.
- Cria usuário admin inicial (quando informado).
- Cria diretórios de runtime e logs.
- Gera lock de instalação em `storage/install.done`.
- Tenta remover automaticamente `public/install.php`.

## Segurança pós-instalação

- Se o instalador não for removido automaticamente, apague `public/install.php` manualmente.
- Troque senhas temporárias usadas na instalação.
- Mantenha `app/config/local.php` fora do versionamento.

## Diagnóstico

- Log da aplicação: `storage/logs/app-error.log`
- Logs do instalador: `storage/logs/install-AAAAMMDD-HHMMSS.log`
