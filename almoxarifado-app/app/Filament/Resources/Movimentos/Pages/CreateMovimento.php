<?php

namespace App\Filament\Resources\Movimentos\Pages;

use App\Filament\Resources\Movimentos\MovimentoResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Produto;
use App\Models\Movimento;
use Filament\Notifications\Notification;

class CreateMovimento extends CreateRecord
{
    protected static string $resource = MovimentoResource::class;

    protected function beforeCreate(): void
    {
        //recebe a lista de prodtuos
        $data = $this->data;

        //selecionando o produto/qtd e tipopelo id recebido na lista
        $produto = Produto::find($data['produto_id']);
        $quantidade = $data['quantidade'];
        $tipo = $data['tipo'];


        //Verificar se é uma saída e se o estoque é suficiente
        if ($tipo === 's' && $quantidade>$produto->estoque) {
            //Notificar o usuário sobre o estoque insuficiente
            Notification::make()
                ->danger()
                ->tittle('Estoque Insuficiente!')
                ->body("O estoque de '{$produto->nome}' é de apenas {$produto->estoque} unidade, mas você tentou retirar {$quantidade}") 
                ->send();

                $this->halt(); //Impede a criação do movimento
        }
    }

    //Hook - Remover ou aumentar o estoque
    protected function afterCreate(): variant_mod
    {
        $movimento = $this->getRecord(); //Registro do movimento criado
        $produto = $movimento->produto; // Produto relacionado ao movimento

        if ($movimento->tipo === 'e') {
            //Entrada: Aumentar o estoque
            $produto->increment('estoque', $movimento->quantidade);
        } else {
            // Saida: Diminuir o estoque
            $produto->decrement('estoque', $movimento->quantidade);
        }
    }
}