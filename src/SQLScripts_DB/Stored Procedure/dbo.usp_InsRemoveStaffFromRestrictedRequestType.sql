USE [ALLOCATE7]
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_InsRemoveStaffFromRestrictedRequestType]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_InsRemoveStaffFromRestrictedRequestType] 
	-- Add the parameters for the stored procedure here
    @intRequestType			    INTEGER,
	@strStaffLogin			VARCHAR(50),
	@strAdminLogin			VARCHAR(50)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
		DECLARE @return_value int
	BEGIN TRY
		declare @Fullname NVARCHAR(100);
		declare @staffID NVARCHAR(50);
		declare @RequestTypeName NVARCHAR(100);
		declare @RequestGroupName NVARCHAR(10);
		declare @History NVARCHAR(max);	
		-- Get the Admin Login Name
		SELECT @Fullname = Forename + '' '' + Surname 
		FROM  Staffdetails as Staff
		WHERE (NetLogin = @strAdminLogin)
		GROUP BY     Forename + '' '' + Surname

		-- Get the type and group descriptions
		SELECT @RequestTypeName= RequestTypes.description, @RequestGroupName = LeaveRequestGroups.Description
        FROM RequestTypes 
		INNER JOIN LeaveRequestGroups ON RequestTypes.GroupID = LeaveRequestGroups.ID
        WHERE (RequestTypes.ID = @intRequestType)

		DELETE FROM RequestTypesStaffLink
		WHERE Login = @strStaffLogin AND RequestTypeID = @intRequestType 

	   set @History = ''Record updated by '' +  @Fullname + '' on '' +  CONVERT(VARCHAR, GETDATE(), 103) + '' at '' + CONVERT(VARCHAR(5),getdate(),108)   + ''.<br>Access to Request Type '''''' + @RequestTypeName + '''''' belonging to '''''' + @RequestGroupName + '''''' was removed.<hr>''

		-- Update the History
		SELECT @staffID = StaffID 
		FROM Staffdetails as Staff
		WHERE (NetLogin = @strStaffLogin)

		UPDATE StaffConfig
		SET  History = ISNULL(History, '''') + @History
		WHERE (StaffID = @staffID)
		IF @@ROWCOUNT = 0 
		INSERT INTO  StaffConfig
			         (StaffID, History)
		VALUES        (@staffID, @History)
		SELECT  ''ReturnValue'' = 0;
		RETURN 0;
			    
			    
	END TRY
	BEGIN CATCH
	    SELECT  
		 ERROR_NUMBER() AS ErrorNumber  
        ,ERROR_SEVERITY() AS ErrorSeverity  
        ,ERROR_STATE() AS ErrorState  
        ,ERROR_PROCEDURE() AS ErrorProcedure  
        ,ERROR_LINE() AS ErrorLine  
        ,ERROR_MESSAGE() AS ErrorMessage; 
		RETURN 1;	

	    --SELECT  ''ReturnValue'' = 1;
		RETURN 1;
	    REVERT
	END CATCH

END
'
EXEC dbo.sp_executesql @strSQL

GO
