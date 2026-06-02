<?php
echo "Payroll periods:\n";
print_r(DB::table('payrolls')->select('period')->distinct()->orderBy('period')->pluck('period')->toArray());
echo "Count per period:\n";
$rows = DB::table('payrolls')->selectRaw('period, count(*) as total')->groupBy('period')->orderBy('period')->get();
foreach ($rows as $r) {
    echo "  {$r->period}: {$r->total}\n";
}
