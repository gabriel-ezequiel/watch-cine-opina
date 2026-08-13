<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\PublicationType;
use App\Enums\VoteType;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicationInteractionTest extends TestCase
{
    /*
     * RefreshDatabase garante que cada teste seja executado
     * com um banco de dados limpo, evitando que dados de um
     * teste interfiram em outro.
     */
    use RefreshDatabase;

    /**
     * Regra de negócio:
     *
     * Quando um usuário vota em uma publicação aberta,
     * ele deve automaticamente passar a acompanhar essa publicação.
     *
     * Esse comportamento é importante porque o requisito determina
     * que todo usuário que votar também deve acompanhar a publicação.
     */
    public function test_user_can_vote_and_automatically_follow_publication(): void
    {
        // Usuário que irá realizar a interação.
        $user = User::factory()->create();

        // Autor da publicação.
        $author = User::factory()->create();

        // Criamos uma publicação aberta para permitir a votação.
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'type' => PublicationType::MOVIE,
            'status' => PublicationStatus::OPEN,
        ]);

        // Simula o usuário autenticado na aplicação.
        $this->actingAs($user);

        /*
         * Executa o mesmo método Livewire utilizado pelo botão
         * de votação no card da publicação.
         */
        Livewire::test('publication.card', [
            'publication' => $publication,
        ])
            ->call('vote', VoteType::RECOMMEND);

        /*
         * O voto precisa existir no banco.
         */
        $this->assertDatabaseHas('votes', [
            'user_id' => $user->id,
            'publication_id' => $publication->id,
            'vote' => VoteType::RECOMMEND->value,
        ]);

        /*
         * Além do voto, o usuário deve ser automaticamente
         * cadastrado como seguidor da publicação.
         */
        $this->assertDatabaseHas('follows', [
            'user_id' => $user->id,
            'publication_id' => $publication->id,
        ]);
    }

    /**
     * Regra de negócio:
     *
     * Uma publicação encerrada não pode receber novos votos.
     *
     * O bloqueio precisa existir no backend e não somente
     * na interface. Dessa forma, mesmo que alguém tente
     * chamar diretamente o método Livewire, o voto não será criado.
     */
    public function test_user_cannot_vote_on_a_finished_publication(): void
    {
        // Usuário que tentará votar.
        $user = User::factory()->create();

        // Autor da publicação.
        $author = User::factory()->create();

        // A publicação começa encerrada.
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'type' => PublicationType::MOVIE,
            'status' => PublicationStatus::CLOSED,
        ]);

        // Simula o usuário autenticado.
        $this->actingAs($user);

        /*
         * Tentativa de votar através do componente Livewire.
         *
         * O método vote() deve identificar que a publicação
         * não está mais aberta e interromper a operação.
         */
        Livewire::test('publication.card', [
            'publication' => $publication,
        ])
            ->call('vote', VoteType::RECOMMEND);

        /*
         * Nenhum voto deve ter sido criado.
         */
        $this->assertDatabaseMissing('votes', [
            'user_id' => $user->id,
            'publication_id' => $publication->id,
        ]);

        /*
         * Como não houve voto, também não deve existir
         * acompanhamento criado automaticamente.
         */
        $this->assertDatabaseMissing('follows', [
            'user_id' => $user->id,
            'publication_id' => $publication->id,
        ]);
    }

    /**
     * Regra de negócio:
     *
     * O autor pode excluir sua publicação somente quando
     * não existe nenhuma interação.
     *
     * Neste teste, outro usuário vota na publicação.
     * Depois disso, o autor tenta excluí-la.
     *
     * A publicação deve continuar existindo no banco.
     */
    public function test_author_cannot_delete_publication_with_interaction(): void
    {
        // Dono da publicação.
        $author = User::factory()->create();

        // Outro usuário que irá interagir com a publicação.
        $voter = User::factory()->create();

        // Publicação criada pelo autor.
        $publication = Publication::factory()->create([
            'user_id' => $author->id,
            'type' => PublicationType::MOVIE,
            'status' => PublicationStatus::OPEN,
        ]);

        /*
         * Primeiro autenticamos o usuário que irá votar.
         */
        $this->actingAs($voter);

        /*
         * O voto representa uma interação com a publicação.
         * Como consequência, o usuário também passa a segui-la.
         */
        Livewire::test('publication.card', [
            'publication' => $publication,
        ])
            ->call('vote', VoteType::RECOMMEND);

        /*
         * Confirmamos que a interação realmente foi criada
         * antes de testar a tentativa de exclusão.
         */
        $this->assertDatabaseHas('votes', [
            'user_id' => $voter->id,
            'publication_id' => $publication->id,
            'vote' => VoteType::RECOMMEND->value,
        ]);

        /*
         * Agora autenticamos o autor da publicação.
         */
        $this->actingAs($author);

        /*
         * O autor tenta excluir a própria publicação.
         *
         * A regra de negócio deve impedir a exclusão porque
         * já existe uma interação de outro usuário.
         */
        Livewire::test('publication.card', [
            'publication' => $publication,
        ])
            ->call('deletePublication');

        /*
         * A publicação deve continuar existindo.
         */
        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
        ]);
    }
}
