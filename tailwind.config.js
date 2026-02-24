module.exports = {
  darkMode: 'class', // Enable class-based dark mode
  content: [
    "./resources/**/*.{html,js,php,vue,blade.php}",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php"
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#ecf3ff',
          100: '#dde9ff',
          500: '#465fff',
          600: '#3641f5',
        },
        gray: {
          50: '#f9fafb',
          100: '#f2f4f7',
          900: '#101828',
        },
      },
      fontFamily: {
        outfit: ['Outfit', 'sans-serif'],
      },
      fontSize: {
        '2xl': '72px',
        'xl': '60px',
        'lg': '48px',
        'md': '36px',
        'sm': '30px',
        'theme': {
          'xl': '20px',
          'sm': '14px',
        },
      },
      screens: {
        '2xsm': '375px',
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1280px',
        '2xl': '1536px',
      },
    },
  },
  plugins: [],
}