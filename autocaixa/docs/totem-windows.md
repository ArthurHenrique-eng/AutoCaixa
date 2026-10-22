# Totem — Windows, Microsoft Edge em modo quiosque e Acesso Atribuído

**Estado:** documentação de preparação. Nenhum passo deste documento foi testado no equipamento real.
Antes de aplicar, confira cada comando e flag na documentação oficial da Microsoft.

Referência oficial indicada no prompt mestre:
https://learn.microsoft.com/pt-br/windows/configuration/assigned-access/configure-single-app-kiosk

## 1. Pré-requisitos

- [ ] Edição do Windows compatível com Acesso Atribuído (confirmar na documentação da Microsoft para a versão instalada).
- [ ] Microsoft Edge atualizado.
- [ ] Conta local dedicada ao totem, sem privilégios de administrador (ex.: `totem`).
- [ ] Conta administrativa separada, usada apenas pela loja ou pelo técnico.
- [ ] Endereço HTTPS público do sistema, funcionando.

## 2. Usuário do totem

1. Crie uma conta local padrão, sem privilégios de administrador.
2. Defina uma senha forte, guardada pela loja.
3. Não use essa conta para navegar ou instalar programas.

## 3. Acesso Atribuído (quiosque de aplicativo único)

1. Abra **Configurações > Contas > Acesso atribuído** (ou o caminho equivalente na sua versão do Windows).
2. Escolha **Aplicativo único (quiosque)** e selecione a conta `totem`.
3. Selecione **Microsoft Edge** como aplicativo.
4. Defina o URL: `https://SEU-DOMINIO/` e os argumentos de quiosque (ver seção 4).
5. Salve e reinicie para testar.

## 4. Argumentos do Edge em modo quiosque

Os argumentos a seguir foram indicados como exemplo. **Confirmar os nomes exatos na documentação do Edge antes de usar.**

```
--kiosk https://SEU-DOMINIO/ --edge-kiosk-type=fullscreen --kiosk-idle-timeout-minutes=2
```

- `--kiosk`: abre a URL em modo quiosque.
- Tipo de tela cheia: confirme se a opção `fullscreen` é a adequada para o totem.
- Tempo de inatividade: retorna o totem à tela inicial. Ajuste conforme a operação.

## 5. Inicialização automática e recuperação

- O Acesso Atribuído inicia o Edge automaticamente ao entrar com a conta `totem`.
- Teste: reinicie o computador e confirme que o totem volta sozinho ao quiosque.
- Teste: desligue a rede e confirme que o totem mostra a mensagem de indisponibilidade, sem registrar venda paga.
- Teste: religue a rede e confirme que o totem volta a funcionar sem intervenção.

## 6. Proteções que não dependem do quiosque

- O quiosque não é segurança. O backend valida tudo, mesmo que alguém acesse o site por outro computador.
- Não bloqueie o Windows apenas com JavaScript, CSS ou atalhos de teclado.
- Mantenha o sistema operacional e o Edge atualizados.

## 7. Manutenção

- Para acessar o Windows como administrador, use a conta administrativa separada, nunca a conta `totem`.
- Para desativar temporariamente o quiosque, use a conta administrativa e revise a configuração do Acesso Atribuído.
- Registre cada alteração de configuração na documentação da loja.
