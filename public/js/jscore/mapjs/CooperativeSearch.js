export default class CooperativeSearch {
    constructor(config) {
        this.config = {
            searchUrl: config.searchUrl || '',
            csrfToken: config.csrfToken || '',
            ...config
        };

        this.state = {
            offset: 0,
            hasMore: true,
            isLoading: false
        };

        this.init();
    }

    init() {
        $(document).on('click', '.head_search_coop button', () => this.search());
        $(document).on('click', '#load-more', () => this.loadMore());
    }

    async search() {
        const nameCoopSelect = $('.head_search_coop_input input[name="name_coop"]').val();

        if (nameCoopSelect.length < 3) {
            this.showMessage('Название слишком короткое');
            return;
        }

        this.state.offset = 0;
        this.state.hasMore = true;

        await this.performSearch(nameCoopSelect, true);
    }

    async loadMore() {
        if (this.state.isLoading || !this.state.hasMore) return;

        const nameCoopSelect = $('.head_search_coop_input input[name="name_coop"]').val();
        await this.performSearch(nameCoopSelect, false);
    }

    async performSearch(query, clearResults = true) {
        this.state.isLoading = true;

        if (clearResults) {
            $('.block_name_result_coop').empty();
            $('.block_name_result_coop').append('<span class="span_text">Выполняем запрос...</span>');
        }

        try {
            const response = await $.ajax({
                url: this.config.searchUrl,
                method: 'GET',
                data: {
                    _token: this.config.csrfToken,
                    idMessage: 1,
                    nameCoop: query,
                    offset: this.state.offset
                }
            });

            this.processSearchResults(response, clearResults);
        } catch (error) {
            this.handleSearchError(error);
        } finally {
            this.state.isLoading = false;
        }
    }

    processSearchResults(response, clearResults) {
        if (clearResults) {
            $('.block_name_result_coop').empty();
        }

        if (response.blockMessages && response.blockMessages.length > 0) {
            response.blockMessages.forEach(coop => {
                const html = `
                    <div class="inf_coop">
                        <span>Название: ${coop.name}</span>
                        <span>Председатель: ${coop.fio}</span>
                        <span>Местонахождение: ${coop.address}</span>
                        <button data-coords="${coop.latitude},${coop.longitude}">
                            Показать на карте
                        </button>
                    </div>`;
                $('.block_name_result_coop').append(html);
            });

            this.state.offset += response.blockMessages.length;
            this.state.hasMore = response.hasMore;

            $('#load-more').toggle(this.state.hasMore);
        } else {
            this.showMessage('Ошибка, похоже нет кооперативов с заданными параметрами');
        }
    }

    handleSearchError(error) {
        const errorCount = parseInt($('.span_text').data('error-count') || 0) + 1;

        $('.span_text').data('error-count', errorCount);
        this.showMessage(`Ошибка поиска ${errorCount > 1 ? `(${errorCount})` : ''}`);
    }

    showMessage(message) {
        $('.block_name_result_coop .span_text').remove();
        $('.block_name_result_coop').append(`<span class="span_text">${message}</span>`);
    }
}

