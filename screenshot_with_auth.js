const puppeteer = require('puppeteer');
const fs = require('fs');

const TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJkMmY4YjBjOC1lMTM1LTQ2NzctYTliNi0yZmI5MGQ5MzRmMWMiLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImRpc3RyaWN0X2J1cmVhdSIsImlhdCI6MTc3ODE1OTgyMiwiZXhwIjoxNzc4NzY0NjIyfQ.D6YcnaQRMMN-rOkK9kXjcA9k2e_dN5jZ4uOLpAKpO1Y';

const wait = (ms) => new Promise(r => setTimeout(r, ms));

async function screenshot(url, output, label) {
  console.log(`Starting: ${label}`);
  const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu', '--disable-dev-shm-usage']
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1920, height: 1080 });
  
  // 先访问页面
  await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
  
  // 注入 token 到 localStorage
  await page.evaluate((token) => {
    localStorage.setItem('token', token);
  }, TOKEN);
  
  // 刷新页面让应用读取 token
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 30000 });
  
  // 等待页面渲染
  await wait(3000);
  
  await page.screenshot({ path: output, fullPage: false });
  await browser.close();
  console.log(`Saved: ${output} (${fs.statSync(output).size} bytes)`);
}

(async () => {
  await screenshot('http://localhost:8000/dashboard', '/workspace/screenshots/final1.png', 'Dashboard');
  await screenshot('http://localhost:8000/funds/public-income', '/workspace/screenshots/final2.png', 'Funds');
  await screenshot('http://localhost:8000/projects/list', '/workspace/screenshots/final3.png', 'Projects');
})();
