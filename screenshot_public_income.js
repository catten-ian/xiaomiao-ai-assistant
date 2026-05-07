const puppeteer = require('puppeteer');
const fs = require('fs');

const TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIyNTg1MzUyMy00MzdhLTRjNDktYmQ4MS1mNmQ1NDlhODU4YjAiLCJ1c2VybmFtZSI6Ind5ZjEiLCJyb2xlIjoicHJvcGVydHkiLCJpYXQiOjE3NzgxNjU3MjgsImV4cCI6MTc4ODc3MDUyOH0.vb1MF0IS8OZUnPCVzwjJ1PvD9xkDEkClESqVWfOco9w';

const wait = (ms) => new Promise(r => setTimeout(r, ms));

(async () => {
  const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu', '--disable-dev-shm-usage']
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1920, height: 2000 });
  
  await page.goto('http://localhost:8000/funds/public-income', { waitUntil: 'domcontentloaded' });
  await page.evaluate((token) => {
    localStorage.setItem('token', token);
  }, TOKEN);
  await page.goto('http://localhost:8000/funds/public-income', { waitUntil: 'networkidle0' });
  await wait(3000);
  
  await page.screenshot({ path: '/workspace/screenshots/public-income.png', fullPage: true });
  console.log('Saved:', fs.statSync('/workspace/screenshots/public-income.png').size, 'bytes');
  
  await browser.close();
})();
