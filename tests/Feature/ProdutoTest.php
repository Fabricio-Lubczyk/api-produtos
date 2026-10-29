<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoTest extends TestCase
{
    use RefreshDatabase; // Faltou essa trait na aula -> serve pra utilizar e dar refresh na memória temporária do sqlite

    public function test_cria_produto_com_sucesso(): void
    {
        $this->postJson('/api/produtos', [
            'nome' => 'Lajota',
            'preco_custo' => 100,
            'preco' => 110,
        ])->assertCreated();

        $this->assertDatabaseHas('produtos', [
            'nome' => 'Lajota']);
    }
    public function test_filtra_produtos_por_nome_e_faixa_de_preco(): void
    {
        Produto::create(['nome' => 'Lajota barata', 'preco_custo' => 5, 'preco' => 10]);
        Produto::create(['nome' => 'Lajota media', 'preco_custo' => 10, 'preco' => 20]);
        Produto::create(['nome' => 'Lajota cara', 'preco_custo' => 15, 'preco' => 30]);
        Produto::create(['nome' => 'Cimento', 'preco_custo' => 10, 'preco' => 20]);

        $this->getJson('/api/produtos?search=Lajota&preco_minimo=20&preco_maximo=20')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome', 'Lajota media');
    }

    public function test_filtra_preco_maximo_zero(): void
    {
        Produto::create(['nome' => 'Gratis', 'preco_custo' => 0, 'preco' => 0]);
        Produto::create(['nome' => 'Pago', 'preco_custo' => 5, 'preco' => 10]);

        $this->getJson('/api/produtos?preco_maximo=0')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome', 'Gratis');
    }

    public function test_atualiza_produto(): void
    {
        $produto = Produto::create(['nome' => 'Lajota', 'preco_custo' => 10, 'preco' => 20]);

        $this->putJson('/api/produtos/'.$produto->id, ['nome' => 'Lajota nova'])
            ->assertOk()
            ->assertJsonPath('data.nome', 'Lajota nova');

        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'nome' => 'Lajota nova']);
    }

    public function test_exclui_produto(): void
    {
        $produto = Produto::create(['nome' => 'Lajota', 'preco_custo' => 10, 'preco' => 20]);

        $this->deleteJson('/api/produtos/'.$produto->id)->assertNoContent();

        $this->assertDatabaseMissing('produtos', ['id' => $produto->id]);
    }
}
