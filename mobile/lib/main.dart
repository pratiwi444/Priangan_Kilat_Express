import 'package:flutter/material.dart';

import 'api.dart';
import 'pages/ganti_password_page.dart';
import 'pages/home_page.dart';
import 'pages/login_page.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Api.muat();
  runApp(const KurirApp());
}

class KurirApp extends StatelessWidget {
  const KurirApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Kurir PKE',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(colorSchemeSeed: Colors.teal, useMaterial3: true),
      initialRoute: Api.sudahLogin ? '/home' : '/login',
      routes: {
        '/login': (_) => const LoginPage(),
        '/ganti': (_) => const GantiPasswordPage(),
        '/home': (_) => const HomePage(),
      },
    );
  }
}