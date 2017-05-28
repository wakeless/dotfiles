# Add any auto-loaded Atom code on init here.
atom.commands.add 'atom-text-editor', 'custom:save-and-add', ->
  editor = atom.workspace.getActiveTextEditor()
  editor.save()
  atom.commands.dispatch(document.querySelector('atom-text-editor'), 'git-plus:add')
