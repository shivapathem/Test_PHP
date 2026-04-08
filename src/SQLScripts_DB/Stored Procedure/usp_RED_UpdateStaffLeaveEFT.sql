USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_RED_UpdateStaffLeaveEFT]    Script Date: 2/10/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_RED_UpdateStaffLeaveEFT]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE dbo.usp_RED_UpdateStaffLeaveEFT 
	-- Add the parameters for the stored procedure here
	-- Add the parameters for the stored procedure here
    @intGroup               INTEGER,
	@intEFT					INTEGER,
	@intEFT1				INTEGER,
	@intEFTSummer				INTEGER,
	@strStaffLogin			VARCHAR(50),
	@strEFTNotes			    VARCHAR(MAX),
	@strHistory			    VARCHAR(MAX),
	@modulename varchar(200),
	@currentuserid		INT

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
		DECLARE @return_value int
	BEGIN TRY
	 
		-- Update the entry
        UPDATE      Staff_Web_Config_LeaveGroups_Link
        SET         EFT = @intEFT,
		            EFT1 = @intEFT1,
					EFTSummer = @intEFTSummer,
					EFTNotes = @strEFTNotes
        WHERE       (Login = @strStaffLogin) AND (LeaveGroupID = @intGroup)

		--history add
		DECLARE @newid INT,@historytype VARCHAR(max)
		SELECT  @newid = ID from Staff_Web_Config_LeaveGroups_Link  WHERE  (Login = @strStaffLogin) AND (LeaveGroupID = @intGroup)

		Select @historytype = id from HistoryTypes where HistoryType = @modulename

		INSERT INTO [dbo].[History]([HistoryType],[UserID],[History],[datetime],[AttributeID])
		VALUES (@historytype ,@currentuserid,@strHistory,GETDATE(),@newid)
		SELECT  ''ReturnValue'' = 0;
		RETURN 0;
			    
	END TRY
	BEGIN CATCH
		REVERT;
	    SELECT  
		 ERROR_NUMBER() AS ErrorNumber  
        ,ERROR_SEVERITY() AS ErrorSeverity  
        ,ERROR_STATE() AS ErrorState  
        ,ERROR_PROCEDURE() AS ErrorProcedure  
        ,ERROR_LINE() AS ErrorLine  
        ,ERROR_MESSAGE() AS ErrorMessage; 
		RETURN 1;		
    REVERT;
	END CATCH

END

'
EXEC dbo.sp_executesql @strSQL

GO
