<?php

class PexelsService
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = "https://api.pexels.com/v1/search";
    }

    /**
     * Busca imagens relacionadas ao tema
     *
     * @param string $query
     * @param int $perPage
     * @return array|null Retorna array com 'url' e 'photographer'
     */
    public function buscarImagem(string $query, int $perPage = 5): ?array
    {
        $url = $this->apiUrl . "?query=" . urlencode($query) . "&per_page=" . $perPage;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: " . $this->apiKey
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            throw new Exception('Erro ao conectar com Pexels: ' . curl_error($ch));
        }

        curl_close($ch);

        $resultado = json_decode($response, true);

        if (empty($resultado['photos'])) {
            throw new Exception("Nenhuma imagem encontrada no Pexels para: $query");
        }

        // Seleciona uma imagem aleatória
        $imagemSelecionada = $resultado['photos'][array_rand($resultado['photos'])];

        return [
            'url' => $imagemSelecionada['src']['large'],
            'photographer' => $imagemSelecionada['photographer']
        ];
    }
}
