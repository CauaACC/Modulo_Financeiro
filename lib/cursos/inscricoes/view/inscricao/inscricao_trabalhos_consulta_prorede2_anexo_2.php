<!-- path: /mnt/data/pontuacao_pes_anexo2.html -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Anexo II — PES — Pontuação de Currículo</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    :root { --w: 1100px; --pad: 10px; --radius: 10px; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, Helvetica, sans-serif; background: #f7f7f7; color: #222; }
    .wrap { max-width: var(--w); margin: 24px auto; background: #fff; padding: 18px; border-radius: var(--radius); box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    h1 { font-size: 18px; margin: 0 0 8px; }
    .muted { color: #666; font-size: 12px; }
    .actions { display:flex; gap:8px; flex-wrap: wrap; margin: 8px 0 16px; }
    button { padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; cursor: pointer; background: #fafafa; }
    button:hover { background: #f0f0f0; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border: 1px solid #ddd; padding: var(--pad); vertical-align: top; }
    th { background: #fafafa; text-align: center; }
    td:nth-child(1) { width: 56%; }
    td:nth-child(2), td:nth-child(3) { width: 10%; text-align:center; }
    td:nth-child(4) { width: 14%; text-align:center; }
    td:nth-child(5) { width: 10%; text-align:center; }
    input[type="number"] { width: 90px; padding: 6px; }
    input[readonly] { background: #fcfcfc; border: none; width: 90px; font-weight: 600; }
    .cap-hit { background: #fff6e5; }
    .lock-note { display:block; margin-top: 6px; font-size: 11px; color: #666; }
    tfoot td { font-weight: 700; }
    .note { font-size: 12px; color: #444; }
    @media print { body { background: #fff; } .wrap { box-shadow: none; } .actions { display:none; } }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>ANEXO II — Profissional Especializado de Suporte (PES) — PONTUAÇÃO DE CURRÍCULO</h1>
    <p class="muted">Baseado no Anexo II do Edital 032/2025 (PES).</p>

    <div class="actions">
      <button id="btnClear" type="button">Limpar</button>
      <!-- <button id="btnPrint" type="button">Imprimir</button> -->
    </div>

    <table id="tbl">
      <thead>
        <tr>
          <th>Itens</th>
          <th>Pontos<br/>por item</th>
          <th>Máx.<br/>pontos</th>
          <th>Quantidade<br/><span class="muted">sugerida</span></th>
          <th>Pontos<br/>calculados</th>
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="5"><strong>1. FORMAÇÃO</strong></td></tr>

        <tr data-id="1_1" data-per="10" data-max="10" data-qtycap="1">
          <td>1.1 Ensino Médio com magistério</td>
          <td>10,0</td><td>10</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_2_1" data-per="3" data-max="3" data-qtycap="1" data-excl="grp_1_2">
          <td>1.2.1 Cursando curso técnico de enfermagem
            <span class="lock-note">Obs.: não pontuar em duplicidade com 1.2.2</span>
          </td>
          <td>3,0</td><td>3</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_2_2" data-per="5" data-max="5" data-qtycap="1" data-excl="grp_1_2">
          <td>1.2.2 Curso técnico de enfermagem (concluído)
            <span class="lock-note">Obs.: não pontuar em duplicidade com 1.2.1</span>
          </td>
          <td>5,0</td><td>5</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_3" data-per="5" data-max="5" data-qtycap="1">
          <td>1.3 Cursando graduação (Pedagogia, Educação Física, Enfermagem, Fisioterapia, Fonoaudiologia, Nutrição, Psicologia ou Terapia Ocupacional)</td>
          <td>5,0</td><td>5</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_4_1" data-per="15" data-max="15" data-qtycap="1" data-excl="grp_1_4">
          <td>1.4.1 Graduação completa (Pedagogia, Educação Física, Enfermagem, Fisioterapia, Fonoaudiologia, Nutrição, Psicologia ou Terapia Ocupacional)
            <span class="lock-note">Obs.: não pontuar em duplicidade com 1.4.2</span>
          </td>
          <td>15,0</td><td>15</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_4_2" data-per="15" data-max="15" data-qtycap="1" data-excl="grp_1_4">
          <td>1.4.2 Graduação completa em cursos com licenciatura
            <span class="lock-note">Obs.: não pontuar em duplicidade com 1.4.1</span>
          </td>
          <td>15,0</td><td>15</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_5" data-per="2" data-max="6" data-qtycap="3">
          <td>1.5 Curso de formação complementar (Análise do Comportamento e/ou educação infantil/especial) <strong>até 20 horas</strong></td>
          <td>2,0/curso</td><td>6</td>
          <td><input type="number" min="0" max="3" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_6" data-per="4" data-max="12" data-qtycap="3">
          <td>1.6 Curso de formação complementar (Análise do Comportamento e/ou educação infantil/especial) <strong>com mais de 20 horas</strong></td>
          <td>4,0/curso</td><td>12</td>
          <td><input type="number" min="0" max="3" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_7_1" data-per="6" data-max="6" data-qtycap="1">
          <td>1.7.1 Cursando Especialização ou Pós-graduação lato sensu (Análise do Comportamento e/ou educação infantil/especial)</td>
          <td>6,0</td><td>6</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_7_2" data-per="10" data-max="50" data-qtycap="5">
          <td>1.7.2 Residência/Especialização/Pós-graduação lato sensu <strong>concluída</strong> (Análise do Comportamento e/ou educação infantil/especial)</td>
          <td>10,0</td><td>50</td>
          <td><input type="number" min="0" max="5" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_8_1" data-per="15" data-max="15" data-qtycap="1">
          <td>1.8.1 Cursando Mestrado (Psicologia, Educação, Desenvolvimento ou Análise do Comportamento)</td>
          <td>15,0</td><td>15</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_8_2" data-per="20" data-max="20" data-qtycap="1">
          <td>1.8.2 Mestrado ou Dissertação (Psicologia, Educação, Desenvolvimento ou Análise do Comportamento)</td>
          <td>20,0</td><td>20</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_9_1" data-per="25" data-max="25" data-qtycap="1">
          <td>1.9.1 Cursando Doutorado (Psicologia, Educação, Desenvolvimento ou Análise do Comportamento)</td>
          <td>25,0</td><td>25</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="1_9_2" data-per="30" data-max="30" data-qtycap="1">
          <td>1.9.2 Doutorado ou Tese (Psicologia, Educação, Desenvolvimento ou Análise do Comportamento)</td>
          <td>30,0</td><td>30</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr><td colspan="5"><strong>2. FORMAÇÃO ACADÊMICA</strong></td></tr>

        <tr data-id="2_1" data-per="0.5" data-max="30">
          <td>2.1 Participação em Projetos de Ensino, Pesquisa e/ou Extensão (IES) (Análise do Comportamento e/ou educação infantil/especial)</td>
          <td>0,5/mês</td><td>30</td>
          <td><input type="number" min="0" step="1" placeholder="meses" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="2_2" data-per="0.1" data-max="15">
          <td>2.2 Participação em grupos de estudos (Análise do Comportamento e/ou educação infantil/especial)</td>
          <td>0,1/hora</td><td>15</td>
          <td><input type="number" min="0" step="1" placeholder="horas" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr><td colspan="5"><strong>3. EXPERIÊNCIA PROFISSIONAL</strong></td></tr>

        <tr data-id="3_1" data-per="0.5" data-max="30">
          <td>3.1 Exercício profissional comprovado no Ensino Infantil, Fundamental e/ou Médio (atividades não docentes)</td>
          <td>0,5/mês</td><td>30</td>
          <td><input type="number" min="0" step="1" placeholder="meses" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="3_2" data-per="0.8" data-max="48">
          <td>3.2 Exercício profissional na área de educação especial (atividades não docentes)</td>
          <td>0,8/mês</td><td>48</td>
          <td><input type="number" min="0" step="1" placeholder="meses" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="3_3" data-per="1" data-max="60">
          <td>3.3 Atividades como docente no ensino Infantil, Fundamental, Médio e/ou Educação especial</td>
          <td>1,0/mês</td><td>60</td>
          <td><input type="number" min="0" step="1" placeholder="meses" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <tr data-id="3_4" data-per="1" data-max="60">
          <td>3.4 Atendimento/atuação como AT (Acompanhante Terapêutico) na área clínica com população infantil</td>
          <td>1,0/mês</td><td>60</td>
          <td><input type="number" min="0" step="1" placeholder="meses" /></td>
          <td><input type="text" readonly /></td>
        </tr>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="4" style="text-align:right;">TOTAL CALCULADO</td>
          <td><input id="total" type="text" readonly /></td>
        </tr>
      </tfoot>
    </table>

    <p class="note">
      Regras: teto por linha (“Máx. pontos”). As linhas com “não pontuar em duplicidade” (1.2.* e 1.4.*) são tratadas automaticamente.
    </p>
  </div>

  <script>
    (function () {
      const tbl = document.getElementById('tbl');
      const totalEl = document.getElementById('total');
      const LS_KEY = 'pes_anexo2_v1';

      const exclGroups = [
        { key: 'grp_1_2', ids: ['1_2_1', '1_2_2'] },
        { key: 'grp_1_4', ids: ['1_4_1', '1_4_2'] },
      ];

      function toNumber(v) { return isNaN(v) ? 0 : v; }
      function calcRow(qty, per, max) { return Math.min(qty * per, max); }
      function getRowById(id){ return tbl.querySelector('tbody tr[data-id="' + id + '"]'); }

      function applyExclusions(){
        exclGroups.forEach(g => {
          const rows = g.ids.map(getRowById).filter(Boolean);
          const filled = rows.filter(r => parseFloat((r.querySelector('input[type="number"]').value || '0')) > 0);
          if (filled.length <= 1) return;

          // Mantém a última alterada (data-last=1); se não houver, mantém a última preenchida.
          const keep = rows.find(r => r.dataset.last === '1') || filled[filled.length - 1];
          rows.forEach(r => { if (r !== keep) r.querySelector('input[type="number"]').value = ''; });
        });
      }

      function applyRow(tr) {
        const per = parseFloat(tr.dataset.per);
        const max = parseFloat(tr.dataset.max);
        const qtycap = tr.dataset.qtycap ? parseFloat(tr.dataset.qtycap) : null;
        const qtyInput = tr.querySelector('input[type="number"]');
        const out = tr.querySelector('input[readonly]');

        let qty = parseFloat(qtyInput.value || '0');
        if (qtycap !== null && qty > qtycap) qty = qtycap;
        if (qty < 0) qty = 0;

        const pts = calcRow(qty, per, max);
        out.value = pts.toFixed(2).replace('.', ',');
        tr.classList.toggle('cap-hit', (qty * per) > max || (qtycap !== null && (parseFloat(qtyInput.value || '0') > qtycap)));
      }

      function recalcAll() {
        applyExclusions();

        let total = 0;
        tbl.querySelectorAll('tbody tr[data-id]').forEach(tr => {
          applyRow(tr);
          const val = tr.querySelector('input[readonly]').value.replace('.', '').replace(',', '.');
          total += toNumber(parseFloat(val || '0'));
        });
        totalEl.value = total.toFixed(2).replace('.', ',');
        saveLS();
      }

      function saveLS() {
        const data = {};
        tbl.querySelectorAll('tbody tr[data-id]').forEach(tr => {
          data[tr.dataset.id] = tr.querySelector('input[type="number"]').value || '';
        });
        localStorage.setItem(LS_KEY, JSON.stringify(data));
      }

      function restoreLS() {
        try {
          const raw = localStorage.getItem(LS_KEY);
          if (!raw) return;
          const data = JSON.parse(raw);
          tbl.querySelectorAll('tbody tr[data-id]').forEach(tr => {
            const id = tr.dataset.id;
            if (data[id] !== undefined) tr.querySelector('input[type="number"]').value = data[id];
          });
        } catch (_) {}
      }

      function clearAll() {
        tbl.querySelectorAll('tbody tr[data-id] input[type="number"]').forEach(inp => { inp.value = ''; });
        localStorage.removeItem(LS_KEY);
        recalcAll();
      }

      tbl.addEventListener('input', (e) => {
        if (!e.target.matches('input[type="number"]')) return;
        const tr = e.target.closest('tr[data-id]');
        if (tr) {
          // limpa flags e marca a última alterada
          exclGroups.forEach(g => g.ids.forEach(id => {
            const r = getRowById(id);
            if (r) delete r.dataset.last;
          }));
          tr.dataset.last = '1';
        }
        recalcAll();
      });

      document.getElementById('btnClear').addEventListener('click', clearAll);
      // document.getElementById('btnPrint').addEventListener('click', () => window.print());

      restoreLS();
      recalcAll();
    })();
  </script>
</body>
</html>
