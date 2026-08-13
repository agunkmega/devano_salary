$p = App\Models\Payroll::find(1890);
if ($p) {
    echo 'Deleting payroll id=1890 for employee ' . $p->employee->full_name . ' period ' . $p->period . ' net=' . $p->net_salary . "\n";
    $p->delete();
    echo "DELETED\n";
} else {
    echo "Not found\n";
}
