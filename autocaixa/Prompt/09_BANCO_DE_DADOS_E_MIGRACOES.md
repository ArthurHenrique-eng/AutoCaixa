# Política de banco de dados, integridade e migrações

## Antes de alterar
Identifique SGBD e versão, schema real, migrations, ORM/driver, índices, constraints, transações, volume aproximado e consumidores das tabelas. Não presuma que o banco está vazio nem que o schema local representa produção.

## Consultas e acesso
- Use consultas parametrizadas/prepared statements para valores externos.
- Use allowlist para partes estruturais que não possam ser parametrizadas, como identificadores de coluna/ordenação dinâmica.
- Dê à aplicação apenas privilégios necessários.
- Separe credenciais de runtime das permissões de migração/administração quando viável.
- Evite consultas N+1 e acesso não autorizado entre usuários/tenants.

## Modelo e integridade
- Use constraints, foreign keys, unicidade e checks quando suportados e apropriados.
- Defina nulabilidade e defaults intencionalmente.
- Armazene datas/horas com semântica e fuso definidos.
- Use tipos monetários exatos ou inteiros em unidades mínimas conforme regras do domínio; evite ponto flutuante para valores financeiros.
- Crie índices com base em consultas reais e avalie custo de escrita.
- Evite cascatas destrutivas inesperadas.

## Transações e concorrência
- Agrupe alterações que precisam ocorrer juntas em transação.
- Trate deadlocks/retries com política limitada e segura.
- Use locks, versionamento otimista ou constraints para proteger invariantes concorrentes.
- Não confie somente em “consultar antes e depois inserir” para garantir unicidade.

## Migrações
- Versione mudanças de schema e dados por ferramenta adequada.
- Considere compatibilidade entre versão antiga e nova da aplicação durante deploy.
- Planeje backup, reversão ou correção compensatória.
- Teste migrations em cópia/ambiente descartável representativo antes de produção.
- Não execute `DROP`, truncamento, reset, alteração destrutiva ou migração de produção sem autorização explícita e plano de recuperação.
- Não edite migrations já aplicadas como se ainda não tivessem sido executadas; crie uma nova migration conforme a ferramenta.

## Validação final
Confira schema, índices, constraints, consultas críticas, autorização, transações e comportamento com dados existentes. Documente comandos executados e o que não foi possível validar.
