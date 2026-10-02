#!/usr/bin/env bash

set -euo pipefail

cd /workspace/cartorios
mkdir -p storage/logs

start_process() {
    local name="$1"
    local pattern="$2"
    shift 2

    if pgrep -f -- "$pattern" >/dev/null; then
        echo "$name já está em execução."
        return
    fi

    local log_file="storage/logs/devcontainer-${name}.log"
    nohup "$@" >"$log_file" 2>&1 </dev/null &
    local process_id=$!

    sleep 1
    if ! kill -0 "$process_id" 2>/dev/null; then
        echo "Falha ao iniciar $name. Últimas linhas do log:" >&2
        tail -n 30 "$log_file" >&2 || true
        exit 1
    fi

    echo "$name iniciado; log em $log_file."
}

start_process laravel "artisan serve --host=0.0.0.0 --port=8000" \
    php artisan serve --host=0.0.0.0 --port=8000

# Fila: e-mail de nova proposta (NewProposalReceived é ShouldQueue).
start_process queue "artisan queue:work --tries=3 --timeout=90" \
    php artisan queue:work --tries=3 --timeout=90

echo "Aplicação disponível em http://localhost:8000"
