# Deploy to Railway.com - Laravel Text-to-Speech App

Railway.com is perfect for Laravel applications with native PHP support!

## 🚀 Quick Deployment Steps

### Method 1: One-Click Deploy (Recommended)
1. Go to [railway.app](https://railway.app)
2. Sign up/Login with GitHub
3. Click "**Deploy from GitHub repo**"
4. Select your repository: `shakilanower300/translate_voice`
5. Railway will automatically detect Laravel and deploy! ✨

### Method 2: Railway CLI
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Initialize project
railway init

# Deploy
railway up
```

## ⚙️ Environment Variables

Railway will automatically set most variables, but you need to add:

### Required Variables:
- `APP_KEY` - Generate new key (Railway can auto-generate)
- `APP_ENV` - Set to `production`
- `APP_DEBUG` - Set to `false`
- `ELEVEN_LABS_API_KEY` - `sk_1a24c5398c4e11fae83a0d9a14cd9febecd44ebcf4dacd73`

### Database (Auto-configured):
Railway will automatically use SQLite, but you can add PostgreSQL if needed:
- `DB_CONNECTION=sqlite` (default, no changes needed)

## 📁 Railway Configuration Files

- `nixpacks.toml` - Build configuration with PHP 8.2
- `railway.json` - Railway-specific deploy settings  
- `Procfile` - Process definition

## 🎯 What Railway Does Automatically

✅ **Detects Laravel** - Automatically configures PHP environment
✅ **Installs Dependencies** - Runs `composer install`
✅ **Builds Assets** - Runs `npm run build`
✅ **Database Setup** - Creates SQLite database
✅ **SSL Certificate** - Free HTTPS domain
✅ **Environment Variables** - Auto-generates APP_KEY
✅ **Git Integration** - Auto-deploy on push

## 🔧 Railway Advantages for Laravel

- **Native PHP 8.2 support** 
- **Zero configuration** for most Laravel apps
- **Free tier** with 500 hours/month
- **Automatic HTTPS** domains
- **GitHub integration** with auto-deploys
- **Built-in database** support (SQLite/PostgreSQL)
- **Easy environment management**

## 📊 Expected Results

Your app will be deployed to: `https://your-app-name.up.railway.app`

Features available:
- ✅ Text translation (Google Translate)
- ✅ Text-to-Speech (Web Speech API + ElevenLabs)
- ✅ Voice selection and customization
- ✅ Audio file downloads
- ✅ Translation history
- ✅ Responsive Bootstrap UI
- ✅ Professional loading states

## 🐛 Troubleshooting

1. **Build Issues**: Check Railway build logs
2. **Database**: Ensure migrations run with `--force` flag
3. **Storage**: Railway automatically handles file permissions
4. **Environment**: Use Railway dashboard to set variables

## 🔄 Auto-Deploy Setup

Railway automatically deploys when you push to GitHub:
```bash
git add .
git commit -m "Update app"
git push origin main
# Railway automatically deploys! 🚀
```

## 💰 Pricing

- **Hobby Plan**: $0/month (500 hours)
- **Pro Plan**: $20/month (unlimited)

Perfect for development and production use!