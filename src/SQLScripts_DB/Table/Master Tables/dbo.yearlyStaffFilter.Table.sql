USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

 IF NOT  EXISTS (SELECT 1 FROM sys.objects 
    WHERE object_id = OBJECT_ID(N'[dbo].[yearlyStaffFilter]'))
  BEGIN
		CREATE TABLE [dbo].[yearlyStaffFilter](
			[ID] [tinyint] NOT NULL,
			[DataInput] [text] NULL
		) ON [PRIMARY]
		
  END

GO

IF NOT EXISTS(SELECT 1 FROM yearlyStaffFilter where ID = 1 )
BEGIN
      INSERT INTO yearlyStaffFilter (ID,DataInput)
      VALUES(1,'ChooseUser=0&filter_approved=on&filter_pending=on&selectedyear=2022');
END

