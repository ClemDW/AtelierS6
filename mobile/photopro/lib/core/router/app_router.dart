import 'package:go_router/go_router.dart';
import '../../features/gallery_list/screens/gallery_list_screen.dart';
import '../../features/gallery_view/screens/gallery_view_screen.dart';
import '../../features/gallery_access/screens/gallery_access_screen.dart';
import '../../features/auth/screens/login_screen.dart';
import '../../features/auth/screens/photographer_dashboard_screen.dart';
import '../../features/photo_upload/screens/photo_upload_screen.dart';

final appRouter = GoRouter(
  initialLocation: '/',
  routes: [
    GoRoute(
      path: '/',
      builder: (context, state) => const GalleryListScreen(),
    ),
    GoRoute(
      path: '/gallery/access',
      builder: (context, state) => const GalleryAccessScreen(),
    ),
    GoRoute(
      path: '/gallery/:id',
      builder: (context, state) {
        final id = state.pathParameters['id']!;
        return GalleryViewScreen(galerieId: id);
      },
    ),
    GoRoute(
      path: '/photographer/login',
      builder: (context, state) => const LoginScreen(),
    ),
    GoRoute(
      path: '/photographer/dashboard',
      builder: (context, state) => const PhotographerDashboardScreen(),
    ),
    GoRoute(
      path: '/photographer/upload',
      builder: (context, state) => const PhotoUploadScreen(),
    ),
  ],
);
