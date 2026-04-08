import 'package:dio/dio.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import '../../../core/services/auth_service.dart';

part 'auth_state.dart';

class AuthCubit extends Cubit<AuthState> {
  final AuthService _service;

  AuthCubit({AuthService? service})
      : _service = service ?? AuthService(),
        super(AuthInitial());

  Future<void> checkSession() async {
    final loggedIn = await _service.isLoggedIn();
    if (loggedIn) {
      emit(AuthAuthenticated());
    } else {
      emit(AuthUnauthenticated());
    }
  }

  Future<void> signin(String email, String password) async {
    emit(AuthLoading());
    try {
      await _service.signin(email, password);
      emit(AuthAuthenticated());
    } on DioException {
      emit(const AuthError('Identifiants incorrects. Veuillez réessayer.'));
    } catch (e) {
      emit(const AuthError('Une erreur est survenue. Veuillez réessayer.'));
    }
  }

  Future<void> logout() async {
    await _service.logout();
    emit(AuthUnauthenticated());
  }
}
