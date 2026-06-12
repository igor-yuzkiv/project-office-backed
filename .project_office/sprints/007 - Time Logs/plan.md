

- на бекенді це буде сутсноість з наступними полями
	- date - дата
	- user - за замовчування created_by
	- created_by
	- updated_by
	- time: string - 00:00
	- ще потрібно поле з розрахованою кількість хвилин
	- type: billable, non billable
	- notes: long text (markdown editor)
- на фронті 
	- діалогове вікно для додаваня логу до задача
	- поля
		- task: readonly
		- user: user loop (новий компоент по типу resources/js/widgets/projects/lookup-field), за замовчуванням поточний користувач
		- time 00:00
		- type: drop down зазамовчування billable
		- notes: MarkdownEditor.vue з відключеним preview