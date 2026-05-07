const puppeteer = require('puppeteer');
const fs = require('fs');

const TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIyODA3OGJiNC01OTY5LTQzZDItYjE1My1mYTVhYWNhY2ZkMmYiLCJ1c2VybmFtZSI6ImNob3VzZTEiLCJyb2xlIjoib3duZXIiLCJpYXQiOjE3NzgxNjU3MjksImV4cCI6MTc4ODc3MDUyOX0.1El_FxMgxyZILIEaSWpHR0wd_ZL6-5XhgGcWfN0fnpM';

const wait = (ms) => new Promise(r => setTimeout(r, ms));

(async () => {
  const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu', '--disable-dev-shm-usage']
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1920, height: 2000 });
  
  await page.goto('http://localhost:8000/disclosure', { waitUntil: 'domcontentloaded' });
  await page.evaluate((token) => {
    localStorage.setItem('token', token);
  }, TOKEN);
  await page.goto('http://localhost:8000/disclosure', { waitUntil: 'networkidle0' });
  await wait(3000);
  
  await page.screenshot({ path: '/workspace/screenshots/disclosure.png', fullPage: true });
  console.log('Saved:', fs.statSync('/workspace/screenshots/disclosure.png').size, 'bytes');
  
  await browser.close();
})();
