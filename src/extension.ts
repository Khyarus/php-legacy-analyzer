import * as vscode from 'vscode';
import { analyzePHP } from './analyzer';

const diagnostics =
  vscode.languages.createDiagnosticCollection('php-legacy-analyzer');

const workspaceRoot = vscode.workspace.workspaceFolders?.[0].uri.fsPath;

/**
 * Decoration (texto fantasma inline)
 */
const issueDecoration =
  vscode.window.createTextEditorDecorationType({
    after: {
      margin: '0 0 0 1rem',
      color: '#f78a24',
      fontStyle: 'italic'
    },
    rangeBehavior: vscode.DecorationRangeBehavior.ClosedClosed
  });

function mapSeverity(sev: string): vscode.DiagnosticSeverity {
  switch (sev) {
    case 'error':
      return vscode.DiagnosticSeverity.Error;
    case 'warning':
      return vscode.DiagnosticSeverity.Warning;
    default:
      return vscode.DiagnosticSeverity.Information;
  }
}

/**
 * Analisa UM documento PHP
 */
async function analyzeDocument(document: vscode.TextDocument) {
  if (document.languageId !== 'php') return;

  try {
    const result = await analyzePHP(
      document.uri.fsPath,
      workspaceRoot ?? ''
    );

    const issues: vscode.Diagnostic[] = [];
    const decorations: vscode.DecorationOptions[] = [];

    const editor = vscode.window.visibleTextEditors.find(
      e => e.document.uri.fsPath === document.uri.fsPath
    );

    for (const issue of result.issues ?? []) {

      const diagnosticRange = new vscode.Range(
        issue.startLine - 1,
        0,
        issue.startLine - 1,
        200
      );

      issues.push(
        new vscode.Diagnostic(
          diagnosticRange,
          issue.message,
          mapSeverity(issue.severity)
        )
      );

      if (editor) {
        const line = document.lineAt(issue.startLine - 1);

        decorations.push({
          range: new vscode.Range(
            issue.startLine - 1,
            line.range.end.character,
            issue.startLine - 1,
            line.range.end.character
          ),
          hoverMessage: new vscode.MarkdownString(
            `### ⚠ ${issue.rule}

${issue.description}

#### 💡 Recomendação
${issue.recommendation}
`
          ),
          renderOptions: {
            after: {
              contentText: `⚠ ${issue.rule}`
            }
          }
        });
      }
    }

    diagnostics.set(document.uri, issues);

    if (editor) {
      editor.setDecorations(issueDecoration, decorations);
    }

  } catch (e) {
    console.error('Erro ao analisar', document.uri.fsPath, e);
  }
}

/**
 * Analisa todo o workspace (comando manual)
 */
async function analyzeWorkspace() {
  diagnostics.clear();

  const files = await vscode.workspace.findFiles('**/*.php');

  for (const file of files) {
    const document = await vscode.workspace.openTextDocument(file);
    await analyzeDocument(document);
  }

  vscode.window.showInformationMessage(
    `Análise concluída (${files.length} arquivos PHP)`
  );
}

export function activate(context: vscode.ExtensionContext) {

  context.subscriptions.push(diagnostics, issueDecoration);

  /**
   * Comando manual
   */
  context.subscriptions.push(
    vscode.commands.registerCommand(
      'phpLegacyAnalyzer.analyzeProject',
      analyzeWorkspace
    )
  );

  /**
   * Auto-run: ao salvar
   */
  context.subscriptions.push(
    vscode.workspace.onDidSaveTextDocument(doc => {
      if (doc.languageId === 'php') {
        analyzeDocument(doc);
      }
    })
  );

  /**
   * Auto-run: ao abrir arquivo
   */
  context.subscriptions.push(
    vscode.workspace.onDidOpenTextDocument(doc => {
      if (doc.languageId === 'php') {
        analyzeDocument(doc);
      }
    })
  );

  /**
   * Auto-run: ao trocar de aba
   */
  context.subscriptions.push(
    vscode.window.onDidChangeActiveTextEditor(editor => {
      if (editor?.document.languageId === 'php') {
        analyzeDocument(editor.document);
      }
    })
  );
  
  if (vscode.window.activeTextEditor?.document.languageId === 'php') {
    analyzeDocument(vscode.window.activeTextEditor.document);
  }
}
