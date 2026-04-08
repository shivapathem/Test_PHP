USE [Allocate7]
GO

INSERT INTO [dbo].[SicknessReason]
           ([SicknessName]
           ,[ReasonCode]
           ,[IsActive]
           ,[CreateBy]
           ,[CreatedDate]
           )
     VALUES
           ('Covid'
           ,'SR49'
           ,1
           ,6
           ,GETDATE()
           )
GO


