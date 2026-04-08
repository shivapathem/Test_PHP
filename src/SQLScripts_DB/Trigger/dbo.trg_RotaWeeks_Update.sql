USE [ALLOCATE7]
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.triggers WHERE [name] = N'trg_RotaWeeks_Update' AND [type] ='TR')
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' TRIGGER [dbo].[trg_RotaWeeks_Update] on [dbo].[RotaWeeks] AFTER INSERT,UPDATE  AS BEGIN
Set nocount on;
Declare @RotaId int;
Select @RotaId = RotaID from inserted;
UPDATE MasterRotas SET isExported=0 WHERE RotaID=@RotaId AND isExported != 0
END
'
EXEC dbo.sp_executesql @strSQL

GO