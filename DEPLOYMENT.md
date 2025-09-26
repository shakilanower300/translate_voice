# Text-to-Speech Translator - Deployment Guide

## Deploying to Render

This Laravel application is configured for easy deployment on Render using the included configuration files.

### Prerequisites
- A GitHub repository with your code
- A Render account (free tier available)

### Deployment Steps

1. **Push your code to GitHub** (if not already done):
   ```bash
   git add .
   git commit -m "Prepare for Render deployment"
   git push origin main
   ```

2. **Connect to Render**:
   - Go to [render.com](https://render.com) and sign up/login
   - Click "New +" and select "Web Service"
   - Connect your GitHub repository

3. **Configure the deployment**:
   - Render will automatically detect the `render.yaml` file
   - Alternatively, you can manually configure:
     - **Environment**: PHP
     - **Build Command**: `bash build.sh`
     - **Start Command**: `bash start.sh`
     - **Plan**: Starter (free tier)

4. **Environment Variables** (if not using render.yaml):
   Set these environment variables in Render dashboard:
   ```
   APP_NAME=Text to Speech Translator
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=[Generate a new key]
   LOG_CHANNEL=stderr
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   CACHE_DRIVER=file
   SESSION_DRIVER=file
   ELEVEN_LABS_API_KEY=sk_1a24c5398c4e11fae83a0d9a14cd9febecd44ebcf4dacd73
   ```

5. **Deploy**:
   - Click "Create Web Service"
   - Render will automatically build and deploy your application
   - The process typically takes 5-10 minutes

### Post-Deployment

Once deployed, your application will be available at the URL provided by Render (e.g., `https://your-app-name.onrender.com`).

### Features Included

- ✅ Text translation using Google Translate
- ✅ Text-to-Speech with Web Speech API
- ✅ Premium AI voices with ElevenLabs integration
- ✅ Multiple voice options and languages
- ✅ Audio file download capability
- ✅ Translation history
- ✅ Responsive Bootstrap 5 UI
- ✅ Professional loading states with stop functionality

### Configuration Files

- `render.yaml` - Render service configuration
- `build.sh` - Build script for dependencies and assets
- `start.sh` - Startup script for the application
- `Procfile` - Alternative process file

### Troubleshooting

1. **Build Failures**: Check build logs in Render dashboard
2. **Database Issues**: Ensure SQLite database is properly created in build script
3. **Asset Issues**: Verify npm build process completes successfully
4. **Permission Issues**: Check that storage directories have proper permissions

### Local Development

To run locally:
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate
npm run dev
php artisan serve
```

### Support

For issues with deployment, check:
- Render build logs
- Laravel application logs
- Ensure all environment variables are properly set