IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'[rota]'
          AND Object_ID = Object_ID(N'[dbo].[ScheduledPersonTeam_LINK]'))
BEGIN
	ALTER TABLE [dbo].ScheduledPersonTeam_LINK ADD rota tinyint NULL CONSTRAINT [DF_ScheduledPersonTeam_LINK_rota]  DEFAULT ((1))
				WITH VALUES;

EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'0= show,1-hide,2-leave' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'ScheduledPersonTeam_LINK', @level2type=N'COLUMN',@level2name=N'rota'

END
GO
