/*
 * Chat roteirizado do site (SPEC-006). Sem IA: segue window.NORDIT_CHAT (config/chat.php).
 * No fluxo "Quero uma proposta" faz as perguntas do formulário uma a uma, mostra o resumo e envia
 * para POST /solicitar-proposta em JSON — mesma validação e mesmo e-mail do formulário.
 * Histórico e respostas ficam no sessionStorage da aba (SPEC-002 · RF-04).
 * Todo texto entra no DOM por textContent: nada digitado pelo visitante vira HTML.
 */
(() => {
  const config = window.NORDIT_CHAT;
  const root = document.getElementById('chat-widget');
  if (!config || !root) return;

  const STORAGE_KEY = 'nordit:chat';
  const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const questions = config.proposal.questions;

  const els = {
    panel: root.querySelector('#chat-panel'),
    messages: root.querySelector('[data-chat-messages]'),
    controls: root.querySelector('[data-chat-controls]'),
    toggle: root.querySelector('[data-chat-toggle]'),
    label: root.querySelector('[data-chat-label]'),
    iconOpen: root.querySelector('[data-chat-icon-open]'),
    iconClose: root.querySelector('[data-chat-icon-close]'),
  };

  const initialState = () => ({ messages: [], mode: 'menu', stepId: 'inicio', questionIndex: 0, answers: {}, result: null });
  let state = restore() ?? initialState();
  let isOpen = false;
  let typing = false;
  let busy = false;
  let pendingMulti = [];

  function restore() {
    try {
      return JSON.parse(sessionStorage.getItem(STORAGE_KEY));
    } catch (error) {
      return null;
    }
  }

  function save() {
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (error) {
      // Sem sessionStorage (aba privada/bloqueio): o chat funciona, só não sobrevive à navegação.
    }
  }

  const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  function fill(text, vars) {
    return text.replace(/\{(\w+)\}/g, (match, key) => vars[key] ?? '');
  }

  function vars() {
    const firstName = (state.answers.contact_name ?? '').trim().split(/\s+/)[0] ?? '';
    return { ...state.answers, first_name: firstName, protocol: state.result?.protocol ?? '' };
  }

  // ---------- Conversa ----------

  async function say(texts) {
    typing = true;
    render();
    for (const text of texts) {
      await delay(config.typingDelay);
      state.messages.push({ from: 'bot', text: fill(text, vars()) });
      render();
    }
    typing = false;
    save();
    render();
  }

  function userSays(text) {
    state.messages.push({ from: 'user', text });
    save();
    render();
  }

  async function playStep(stepId) {
    state.mode = 'menu';
    state.stepId = stepId;
    save();
    await say(config.steps[stepId].messages);
  }

  async function startProposal() {
    state.mode = 'question';
    state.questionIndex = 0;
    state.answers = {};
    state.result = null;
    save();
    await say(config.proposal.intro);
    await ask();
  }

  async function ask() {
    pendingMulti = [];
    await say([questions[state.questionIndex].ask]);
  }

  function invalidMessage(question, value) {
    const messages = config.proposal.invalid;
    const text = Array.isArray(value) ? '' : String(value ?? '').trim();

    if (!question.optional && (Array.isArray(value) ? value.length === 0 : text === '')) return messages.required;
    if (question.max && text.length > question.max) return messages.max;
    if (question.type === 'email' && !EMAIL_PATTERN.test(text)) return messages.email;
    if (question.type === 'tel' && text !== '' && !/^\d{10,11}$/.test(text.replace(/\D/g, ''))) return messages.phone;
    return null;
  }

  async function answer(value, display) {
    const question = questions[state.questionIndex];
    userSays(display);

    const problem = invalidMessage(question, value);
    if (problem) {
      await say([problem]);
      return;
    }

    state.answers[question.field] = value;
    state.questionIndex += 1;
    save();

    if (state.questionIndex < questions.length) {
      await ask();
    } else {
      await showSummary();
    }
  }

  function displayValue(question) {
    const value = state.answers[question.field];
    if (question.type === 'choice') return question.choices.find((choice) => choice.value === value)?.label ?? '—';
    if (question.type === 'multi') {
      const labels = question.choices.filter((choice) => (value ?? []).includes(choice.value)).map((choice) => choice.label);
      return labels.length ? labels.join(', ') : config.proposal.none_label;
    }
    return value ? String(value) : '—';
  }

  async function showSummary() {
    state.mode = 'summary';
    save();
    const lines = questions.map((question) => `${question.label}: ${displayValue(question)}`).join('\n');
    await say([config.proposal.summary_intro, lines]);
  }

  async function submit() {
    busy = true;
    userSays(config.proposal.confirm_label);
    state.messages.push({ from: 'bot', text: config.proposal.sending });
    render();

    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let response;
    let data = {};

    try {
      response = await fetch(config.urls.submit, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ ...state.answers, source: 'chat' }),
      });
      data = await response.json().catch(() => ({}));
    } catch (error) {
      response = null;
    }

    busy = false;

    if (response && (response.status === 200 || response.status === 201)) {
      state.mode = 'done';
      state.result = { protocol: data.protocol, trackingUrl: data.tracking_url };
      save();
      await say(data.duplicate ? config.proposal.duplicate : config.proposal.success);
      return;
    }

    if (response?.status === 422 && data.errors) {
      // Volta à pergunta do primeiro campo recusado pelo servidor, com a mensagem dele.
      const [key, fieldMessages] = Object.entries(data.errors)[0];
      const field = key.split('.')[0];
      const index = questions.findIndex((question) => question.field === field);
      state.mode = 'question';
      state.questionIndex = index >= 0 ? index : 0;
      save();
      await say([fieldMessages[0]]);
      await ask();
      return;
    }

    const errors = config.proposal.errors;
    const message = response?.status === 419 ? errors.expired : response?.status === 429 ? errors.throttled : errors.generic;
    await say([message]);
  }

  // ---------- Controles (respostas rápidas e campo de texto) ----------

  function button(label, onClick, extraClass = '') {
    const element = document.createElement('button');
    element.type = 'button';
    element.className = `chat-option ${extraClass}`.trim();
    element.textContent = label;
    element.addEventListener('click', onClick);
    return element;
  }

  function textInput({ type, placeholder, max, optional }, onSubmit) {
    const form = document.createElement('form');
    form.className = 'chat-input';
    const field = document.createElement(type === 'textarea' ? 'textarea' : 'input');
    field.className = 'chat-input__field';
    field.placeholder = placeholder ?? '';
    if (max) field.maxLength = max;
    if (type === 'textarea') field.rows = 2;
    else field.type = { email: 'email', tel: 'tel' }[type] ?? 'text';
    if (type === 'tel') field.inputMode = 'tel';
    field.setAttribute('aria-label', placeholder ?? 'Sua resposta');

    const send = document.createElement('button');
    send.type = 'submit';
    send.className = 'chat-input__send';
    send.textContent = 'Enviar';

    form.append(field, send);
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const value = field.value.trim();
      if (value === '' && !optional) return;
      onSubmit(value);
    });
    field.addEventListener('keydown', (event) => {
      if (type === 'textarea' && event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        form.requestSubmit();
      }
    });
    return form;
  }

  function menuControls(nodes) {
    const step = config.steps[state.stepId];
    if (step.input) {
      nodes.push(textInput(step.input, (protocol) => {
        userSays(protocol);
        window.location.href = `${config.urls.tracking}?protocolo=${encodeURIComponent(protocol.toUpperCase())}`;
      }));
    }
    const options = document.createElement('div');
    options.className = 'chat-panel__options';
    for (const option of step.options ?? []) {
      options.append(button(option.label, () => onMenuOption(option)));
    }
    nodes.push(options);
  }

  function onMenuOption(option) {
    if (option.action === 'proposal') {
      userSays(option.label);
      startProposal();
      return;
    }
    userSays(option.label);
    if (option.action === 'whatsapp') window.open(config.urls.whatsapp, '_blank', 'noopener');
    if (option.next) playStep(option.next);
  }

  function questionControls(nodes) {
    const question = questions[state.questionIndex];
    const options = document.createElement('div');
    options.className = 'chat-panel__options';

    if (question.type === 'choice') {
      for (const choice of question.choices) {
        options.append(button(choice.label, () => answer(choice.value, choice.label)));
      }
    } else if (question.type === 'multi') {
      for (const choice of question.choices) {
        const pressed = pendingMulti.includes(choice.value);
        const element = button(choice.label, () => {
          pendingMulti = pressed ? pendingMulti.filter((value) => value !== choice.value) : [...pendingMulti, choice.value];
          render();
        }, pressed ? 'chat-option--selected' : '');
        element.setAttribute('aria-pressed', pressed ? 'true' : 'false');
        options.append(element);
      }
      options.append(button(config.proposal.done_label, () => {
        const labels = question.choices.filter((choice) => pendingMulti.includes(choice.value)).map((choice) => choice.label);
        answer([...pendingMulti], labels.length ? labels.join(', ') : config.proposal.none_label);
      }, 'chat-option--primary'));
    } else {
      nodes.push(textInput(question, (value) => answer(value, value)));
      if (question.optional) {
        options.append(button(config.proposal.skip_label, () => answer('', config.proposal.skip_label)));
      }
    }

    nodes.push(options);
  }

  function summaryControls(nodes) {
    const options = document.createElement('div');
    options.className = 'chat-panel__options';
    options.append(
      button(config.proposal.confirm_label, submit, 'chat-option--primary'),
      button(config.proposal.restart_label, () => {
        userSays(config.proposal.restart_label);
        startProposal();
      }),
    );
    const link = document.createElement('a');
    link.className = 'chat-option';
    link.href = config.urls.form;
    link.textContent = config.proposal.form_label;
    options.append(link);
    nodes.push(options);
  }

  function doneControls(nodes) {
    const options = document.createElement('div');
    options.className = 'chat-panel__options';
    if (state.result?.trackingUrl) {
      const link = document.createElement('a');
      link.className = 'chat-option chat-option--primary';
      link.href = state.result.trackingUrl;
      link.textContent = config.proposal.track_label;
      options.append(link);
    }
    options.append(button('↩ Voltar ao início', () => {
      userSays('↩ Voltar ao início');
      playStep('menu');
    }));
    nodes.push(options);
  }

  // ---------- Desenho ----------

  function render() {
    els.messages.replaceChildren(...state.messages.map((message) => {
      const bubble = document.createElement('p');
      bubble.className = `chat-message chat-message--${message.from}`;
      bubble.textContent = message.text;
      return bubble;
    }));

    if (typing) {
      const dots = document.createElement('p');
      dots.className = 'chat-message chat-message--bot chat-message--typing';
      dots.setAttribute('aria-label', 'digitando');
      dots.append(document.createElement('i'), document.createElement('i'), document.createElement('i'));
      els.messages.append(dots);
    }

    const nodes = [];
    if (!typing && !busy) {
      if (state.mode === 'menu') menuControls(nodes);
      else if (state.mode === 'question') questionControls(nodes);
      else if (state.mode === 'summary') summaryControls(nodes);
      else if (state.mode === 'done') doneControls(nodes);
    }
    els.controls.replaceChildren(...nodes);
    els.controls.hidden = nodes.length === 0;
    if (isOpen) els.controls.querySelector('.chat-input__field')?.focus();

    els.messages.scrollTop = els.messages.scrollHeight;
  }

  function setOpen(open) {
    isOpen = open;
    els.panel.hidden = !open;
    els.label.hidden = open;
    els.iconOpen.hidden = open;
    els.iconClose.hidden = !open;
    els.toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    els.toggle.setAttribute('aria-label', open ? 'Fechar chat' : 'Abrir chat');
    if (open) render();
  }

  function open(startWithProposal = false) {
    setOpen(true);
    const inProposal = ['question', 'summary'].includes(state.mode);
    if (startWithProposal && !inProposal) {
      userSays('Quero uma proposta');
      startProposal();
    } else if (state.messages.length === 0) {
      playStep('inicio');
    }
  }

  els.toggle.addEventListener('click', () => (isOpen ? setOpen(false) : open()));
  root.querySelector('[data-chat-close]').addEventListener('click', () => setOpen(false));

  // Botões da página que abrem o chat (ex.: "Falar com o assistente" na página "em breve").
  document.querySelectorAll('[data-chat-open]').forEach((trigger) => {
    trigger.addEventListener('click', (event) => {
      event.preventDefault();
      open(trigger.dataset.chatOpen === 'proposal');
    });
  });

  // Estados de espera não sobrevivem a um recarregamento; voltar ao passo estável.
  if (state.mode === 'question' && state.questionIndex >= questions.length) state.mode = 'summary';
})();
