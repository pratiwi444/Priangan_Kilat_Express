import 'package:flutter_test/flutter_test.dart';
import 'package:kurir_pke/ui.dart';

void main() {
  test('rupiah memberi pemisah ribuan', () {
    expect(rupiah(0), 'Rp0');
    expect(rupiah(950), 'Rp950');
    expect(rupiah(1250000), 'Rp1.250.000');
  });
}