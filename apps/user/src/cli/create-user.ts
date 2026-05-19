import 'reflect-metadata';
import { NestFactory } from '@nestjs/core';
import * as readline from 'readline';
import { AppModule } from '../app.module';
import { RegisterUserHandler, RegisterUserCommand } from '../identity/application/commands/RegisterUser';

function ask(rl: readline.Interface, question: string): Promise<string> {
  return new Promise(resolve => rl.question(question, resolve));
}

function askPassword(question: string): Promise<string> {
  return new Promise(resolve => {
    process.stdout.write(question);
    const { stdin } = process;
    stdin.setRawMode(true);
    stdin.resume();
    stdin.setEncoding('utf8');
    let value = '';

    function onData(chunk: string) {
      if (chunk === '\r' || chunk === '\n') {
        stdin.setRawMode(false);
        stdin.pause();
        stdin.removeListener('data', onData);
        process.stdout.write('\n');
        resolve(value);
      } else if (chunk === '') {
        process.stdout.write('\n');
        process.exit(130);
      } else if (chunk === '') {
        if (value.length > 0) {
          value = value.slice(0, -1);
          process.stdout.write('\b \b');
        }
      } else {
        value += chunk;
        process.stdout.write('*');
      }
    }

    stdin.on('data', onData);
  });
}

async function main() {
  const app = await NestFactory.createApplicationContext(AppModule, { logger: false });
  const handler = app.get(RegisterUserHandler);

  const rl = readline.createInterface({ input: process.stdin, output: process.stdout });

  console.log('\n--- Create user account ---\n');

  const email = (await ask(rl, 'Email: ')).trim();
  rl.close();

  const password = await askPassword('Password: ');
  const confirm = await askPassword('Confirm password: ');

  if (password !== confirm) {
    console.error('\nPasswords do not match.');
    await app.close();
    process.exit(1);
  }

  const command = Object.assign(new RegisterUserCommand(), { email, password });

  try {
    const { id } = await handler.execute(command);
    console.log(`\nUser created — id: ${id}`);
  } catch (err: any) {
    console.error(`\nError: ${err.message ?? err}`);
    await app.close();
    process.exit(1);
  }

  await app.close();
}

main().catch(err => {
  console.error(err);
  process.exit(1);
});
