<?php

class GeminiService
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";
    }

    /**
     * Gera texto baseado no tema e conteúdo fornecidos
     *
     * @param string $tema
     * @param string $conteudo
     * @return string|null
     */
    public function gerarTexto(string $tema, string $conteudo): ?string
    {
        $prompt = "Escreva um pequeno texto sobre o tema \"$tema\" com o conteúdo sobre \"$conteudo\". " .
                "O texto deve conter no máximo 200 palavras em dois parágrafos e ser escrito no português de Portugal " .
                "para ser postado na rede social do trabalho. Deve ter um caráter informativo, envolvente e de curiosidade." .
                "Evite usar linguagem muito formal ou técnica, mas mantenha um tom profissional e acessível." .
                "IMPORTANTE: Retorne APENAS o texto final, sem formatações markdown (não use **, ***, ##, ###), " .
                "sem prefixos como 'Aqui está', 'Segue o texto', 'Claro', ou qualquer outra introdução. " .
                "Comece diretamente com o conteúdo do texto.";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($this->apiUrl . "?key=" . $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            throw new Exception('Erro ao conectar com Gemini: ' . curl_error($ch));
        }

        curl_close($ch);

        $resultado = json_decode($response, true);

        if (isset($resultado['error'])) {
            throw new Exception("Erro na API Gemini: " . $resultado['error']['message']);
        }

        if (empty($resultado['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception("Nenhum texto foi gerado pelo Gemini.");
        }

        return $resultado['candidates'][0]['content']['parts'][0]['text'];
    }
}
