#!/usr/bin/env node

/**
 * Diagnostic script to verify Remotion environment requirements
 * Usage: node verify-env.mjs
 */

import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';

console.log('🔍 Checking Remotion Environment Requirements...\n');

// 1. Check Node.js version
const nodeVersion = process.version;
const major = parseInt(nodeVersion.replace('v', '').split('.')[0], 10);
console.log(`Node.js version: ${nodeVersion}`);
if (major < 18) {
  console.warn('⚠️ Warning: Remotion v4 recommends Node.js 18 or newer.');
} else {
  console.log('✅ Node.js version satisfies Remotion requirements.');
}

// 2. Check vid-remotion directory
const candidatePath = path.resolve(process.cwd(), 'vid-remotion');
if (fs.existsSync(candidatePath)) {
  console.log(`✅ Found Remotion workspace directory at: ${candidatePath}`);
  const pkgPath = path.join(candidatePath, 'package.json');
  if (fs.existsSync(pkgPath)) {
    const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf8'));
    console.log(`   Remotion package version: ${pkg.dependencies?.remotion || 'not found'}`);
  }
} else {
  console.log(`ℹ️ No vid-remotion folder in current CWD (${process.cwd()}).`);
}

// 3. Check FFmpeg availability
try {
  const ffmpegOutput = execSync('ffmpeg -version', { stdio: ['pipe', 'pipe', 'ignore'] }).toString();
  const firstLine = ffmpegOutput.split('\n')[0];
  console.log(`✅ System FFmpeg detected: ${firstLine}`);
} catch {
  console.log('ℹ️ System FFmpeg not in PATH (Remotion will download/use its bundled Chromium & FFmpeg).');
}

console.log('\n🚀 Remotion environment ready.');
