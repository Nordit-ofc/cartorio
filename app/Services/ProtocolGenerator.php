<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Protocolos no formato PREFIXO-AAAA-NNNN (ex.: NRD-2026-0001), sequenciais por prefixo e ano.
 * O contador fica em protocol_sequences e é lido com lock — dois envios simultâneos nunca
 * recebem o mesmo número, e números não são reutilizados.
 */
class ProtocolGenerator
{
    public function next(string $prefix): string
    {
        return DB::transaction(function () use ($prefix): string {
            $year = (int) now()->format('Y');
            $key = ['prefix' => $prefix, 'year' => $year];

            DB::table('protocol_sequences')->insertOrIgnore($key + ['last_number' => 0]);

            $sequence = DB::table('protocol_sequences')->where($key)->lockForUpdate()->first();
            $number = $sequence->last_number + 1;

            DB::table('protocol_sequences')->where('id', $sequence->id)->update(['last_number' => $number]);

            return sprintf('%s-%d-%04d', $prefix, $year, $number);
        });
    }
}
