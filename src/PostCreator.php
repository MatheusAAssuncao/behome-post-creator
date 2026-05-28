<?php

require_once 'GeminiService.php';
require_once 'PexelsService.php';

class PostCreator
{
    private GeminiService $geminiService;
    private PexelsService $pexelsService;
    private string $topicosJsonPath;
    private bool $setAsPosted = true; // Define se o tópico deve ser marcado como postado após execução

    public function __construct(string $geminiApiKey, string $pexelsApiKey, string $topicosJsonPath = 'topicos.json')
    {
        $this->geminiService = new GeminiService($geminiApiKey);
        $this->pexelsService = new PexelsService($pexelsApiKey);
        $this->topicosJsonPath = $topicosJsonPath;
    }

    /**
     * Executa o fluxo completo de criação de post
     */
    public function run(): void
    {
        try {
            // 1. Carrega e seleciona tópico
            $topico = $this->selecionarTopicoAleatorio();

            if (!$topico) {
                echo "Não há tópicos disponíveis para postar.";
                return;
            }

            // 2. Gera texto com Gemini
            $textoGerado = $this->geminiService->gerarTexto(
                $topico['tema'],
                $topico['conteudo']
            );

            // 3. Busca imagem no Pexels
            $imagem = $this->pexelsService->buscarImagem($topico['tema']);

            // 4. Exibe resultados
            $this->exibirResultados($topico, $textoGerado, $imagem);

            // 5. Marca tópico como postado
            if ($this->setAsPosted) {
                $this->marcarTopicoComoPostado($topico);
            }

        } catch (Exception $e) {
            echo "Erro: " . $e->getMessage();
        }
    }

    /**
     * Seleciona um tópico aleatório que ainda não foi postado
     *
     * @return array|null
     */
    private function selecionarTopicoAleatorio(): ?array
    {
        $topicos = json_decode(file_get_contents($this->topicosJsonPath), true);

        $topicosDisponiveis = [];

        foreach ($topicos as $indiceTema => $tema) {
            foreach ($tema['topicos'] as $indiceTopico => $topico) {
                if ($topico['already_posted'] === false) {
                    $topicosDisponiveis[] = [
                        'tema' => $tema['tema'],
                        'conteudo' => $topico['conteudo'],
                        'indice_tema' => $indiceTema,
                        'indice_topico' => $indiceTopico
                    ];
                }
            }
        }

        if (empty($topicosDisponiveis)) {
            return null;
        }

        return $topicosDisponiveis[array_rand($topicosDisponiveis)];
    }

    /**
     * Marca o tópico selecionado como já postado
     *
     * @param array $topico
     */
    private function marcarTopicoComoPostado(array $topico): void
    {
        $topicos = json_decode(file_get_contents($this->topicosJsonPath), true);

        $topicos[$topico['indice_tema']]['topicos'][$topico['indice_topico']]['already_posted'] = true;

        file_put_contents(
            $this->topicosJsonPath,
            json_encode($topicos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Exibe os resultados na tela
     *
     * @param array $topico
     * @param string $texto
     * @param array $imagem
     */
    private function exibirResultados(array $topico, string $texto, array $imagem): void
    {
        echo "<strong><h1>Tópico selecionado</h1></strong>";
        echo "<p>Tema: " . $topico['tema'] . "</p>";
        echo "<p>Conteúdo: " . $topico['conteudo'] . "</p>";
        echo "<h2>Conteúdo gerado</h2>";
        echo "<p>" . $texto . "</p>";
        echo "<p>Imagem selecionada: " . $imagem['url'] . "</p>";
        echo "<p>Fotógrafo: " . $imagem['photographer'] . "</p>";
    }
}
