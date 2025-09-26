# Text-to-Speech Translator

A modern web application built with Laravel that allows users to translate text into multiple languages and convert the translated text to speech with customizable voice settings.

## 🌟 Live Demo
**Access the application at: [http://localhost:8000](http://localhost:8000)**

## ✨ Features

### Core Features
- **Multi-language Translation**: Support for 50+ languages using Google Translate
- **Text-to-Speech**: Convert translated text to speech with customizable settings
- **Voice Customization**: Choose gender, speed, and pitch for generated speech
- **Translation History**: Store and view past translations with MySQL database
- **Audio Download**: Download generated speech files (demo mode uses Web Speech API)
- **Responsive Design**: Modern Bootstrap 5 UI that works on all devices

### Advanced Features
- **Real-time Translation**: Instant translation with error handling
- **Language Detection**: Automatic source language detection
- **Voice Preview**: Test voice settings using Web Speech API
- **History Management**: View and delete past translations
- **Error Handling**: Comprehensive error handling with user-friendly messages
- **Performance Optimized**: Efficient database queries and responsive UI

## 🚀 Quick Start

The application is already set up and running! Just visit: **http://localhost:8000**

### How to Use:
1. **Enter Text**: Type or paste text in the input area (up to 5,000 characters)
2. **Select Languages**: Choose source and target languages from the dropdowns
3. **Translate**: Click "Translate Text" to get the translation
4. **Generate Speech**: Adjust voice settings and click "Generate Speech"
5. **Listen**: The app uses Web Speech API for instant audio playback
6. **View History**: Check the history section to see past translations

## 🛠️ Technical Implementation

### Backend (Laravel)
- **Framework**: Laravel 12 with PHP 8.2
- **Database**: MySQL with migrations for `translations` and `audio_files` tables
- **Translation Service**: Google Translate PHP package (stichoza/google-translate-php)
- **TTS Service**: Web Speech API integration (browser-based)
- **API Endpoints**: RESTful API for translation, speech generation, and history

### Frontend
- **UI Framework**: Bootstrap 5 with custom CSS
- **JavaScript**: Vanilla ES6+ with fetch API for AJAX requests
- **Speech Synthesis**: Web Speech API for real-time TTS
- **Responsive Design**: Mobile-first approach with modern animations

### Database Schema
- **translations**: Stores text, language pairs, and metadata
- **audio_files**: Links to translations with voice settings and file info
- **Indexing**: Optimized queries with proper indexing

## 🌍 Supported Languages

### Popular Languages (12 main)
- English (en), Spanish (es), French (fr), German (de)
- Italian (it), Portuguese (pt), Russian (ru), Japanese (ja)
- Korean (ko), Chinese (zh), Arabic (ar), Hindi (hi)

### Extended Support (50+ total)
- Dutch, Swedish, Danish, Norwegian, Polish, Czech
- Slovak, Hungarian, Romanian, Bulgarian, Croatian
- Serbian, Slovenian, Estonian, Latvian, Lithuanian
- Finnish, Turkish, Greek, Hebrew, Thai, Vietnamese
- Indonesian, Malay, Filipino, Swahili, Ukrainian
- And many more...

## 🔧 Installation (If needed)

If you want to set up a fresh copy:

```bash
# Clone and setup
git clone <repo-url>
cd text-to-speech-app
composer install --ignore-platform-reqs

# Environment
cp .env.example .env
php artisan key:generate

# Database (MySQL)
mysql -u root -e "CREATE DATABASE text_to_speech_app"
php artisan migrate

# Start server
php artisan serve --host=0.0.0.0 --port=8000
```

## 📱 Features in Action

### Translation Flow
1. **Input**: Enter text up to 5,000 characters
2. **Language Selection**: Auto-detect source or manually select
3. **Translation**: Instant translation with error handling
4. **Storage**: Automatic saving to database for history

### Speech Generation
1. **Voice Settings**: Choose gender, adjust speed (0.25x-2x), pitch (-10 to +10)
2. **Generation**: Uses Web Speech API for instant playback
3. **Playback**: Real-time audio synthesis in the browser
4. **History**: All generated speeches are tracked

### History Management
- View past translations with language pairs and timestamps
- See associated audio files with voice settings
- Delete translations with confirmation
- Refresh history dynamically

## 🌐 API Endpoints

### Translation
- `POST /api/translate` - Translate text
- `GET /api/languages` - Get supported languages

### Speech
- `POST /api/generate-speech` - Generate speech
- `GET /api/download/{audioFileId}` - Download audio (demo)

### History
- `GET /api/history` - Get translation history
- `DELETE /api/translation/{id}` - Delete translation

## 💡 Technical Highlights

### Error Handling
- Comprehensive validation on all inputs
- User-friendly error messages
- Graceful fallbacks for API failures
- CSRF protection on all forms

### Performance Features
- Efficient database queries with proper indexing
- Real-time character counting
- Optimized frontend with minimal dependencies
- Responsive design for all devices

### Security
- Input sanitization and validation
- SQL injection prevention
- CSRF token protection
- Safe error message handling

## 🚀 Deployment Ready

The application is ready for deployment with:
- Production-ready Laravel configuration
- MySQL database setup
- Comprehensive error handling
- Security best practices
- Performance optimizations

### For Production:
1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure production database
3. Set up proper web server (Nginx/Apache)
4. Enable HTTPS for Web Speech API
5. Consider integrating cloud TTS services (Google Cloud TTS, AWS Polly)

## 🎯 Demo Features

Since this is a demonstration app:
- **Web Speech API**: Used for instant TTS instead of cloud services
- **File Placeholders**: Audio "files" are JSON metadata for demo purposes
- **Local Storage**: All data stored in local MySQL database
- **Real Translation**: Uses actual Google Translate API for translations

## 📞 Support & Next Steps

The application is fully functional and ready to use! 

### Potential Enhancements:
- Integration with Google Cloud TTS for production audio files
- User authentication and personal histories
- Bulk translation features
- Advanced voice settings
- API rate limiting
- Audio file compression

---

**🔗 Access the app**: [http://localhost:8000](http://localhost:8000)

**Tech Stack**: Laravel 12 | PHP 8.2 | MySQL 8 | Bootstrap 5 | Web Speech API

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
