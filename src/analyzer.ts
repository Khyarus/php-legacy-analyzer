import { exec } from 'child_process';
import * as path from 'path';

export function analyzePHP(
  filePath: string,
  workspaceRoot: string
): Promise<any> {

  return new Promise((resolve, reject) => {
    const analyzerPath = path.join(
      __dirname,
      '..',
      'php-analyzer',
      'analyze.php'
    );

    const cmd = `php "${analyzerPath}" "${filePath}"`;

    exec(cmd, { cwd: workspaceRoot || undefined }, (err, stdout, stderr) => {

      if (err) {
        return reject(err);
      }

      if (stderr) {
        return reject(new Error(stderr));
      }

      try {
        const json = JSON.parse(stdout);
        resolve(json);
      } catch (e) {
        reject(new Error('Erro ao parsear JSON do analyzer'));
      }
    });
  });
}
